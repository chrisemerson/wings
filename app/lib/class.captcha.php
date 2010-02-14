<?php
  /************************************/
  /* Captcha Class - by Chris Emerson */
  /* http://www.cemerson.co.uk/       */
  /*                                  */
  /* Version 0.2                      */
  /* 28th May 2009                    */
  /************************************/

  class Captcha {
    private $strCode;
    private $imgCaptchaImage;
    private $arrFonts;
    private $arrBackgrounds;
    private $arrPresetColors;
    private $arrRandomColors;

    /* Config Variables */
    private $strAllowedCharacters = '';
    private $intLength = 0;
    private $strSessionName = '';
    private $blnCaseSensitiveComparison = false;
    private $intImageWidth = 0;
    private $intImageHeight = 0;
    private $intPaddingLeft = 0;
    private $intLetterPadding = 0;
    private $intHeightVariation = 0;
    private $intFontTilt = 0;
    private $intFontSize = 0;
    private $strFontDirectory = "";
    private $strBackgroundImagesDirectory = "";
    private $intDarkestColor = 0;
    private $intLightestColor = 0;
    private $intNumberOfColors = 0;
    private $intNoiseAmount = 0;
    private $intLines = 0;
    private $intLineThickness = 0;
    private $intHorizontalLinesProbability = 0;

    public function __construct ($blnRefreshWithNewCode = false) {
      $this->loadConfig();

      if (isset($_SESSION[$this->strSessionName]) && !$blnRefreshWithNewCode) {
        $this->strCode = $_SESSION[$this->strSessionName];
      } else {
        $this->generateNewCode();
      }//if
    }//function

    /********************/
    /* Public Functions */
    /********************/

    public function getCode () {
      if (empty($this->strCode)) {
        $this->generateNewCode();
      }//if

      return $this->strCode;
    }//function

    public function checkCode ($strCodeToCheck) {
      if ($this->blnCaseSensitiveComparison) {
        return (strcmp($strCodeToCheck, $this->strCode) == 0);
      } else {
        return (strcasecmp($strCodeToCheck, $this->strCode) == 0);
      }//if
    }//function

    public function generateNewCode () {
      $strCode = '';

      for ($i = 0; $i < $this->intLength; $i++) {
        $strCode .= substr($this->strAllowedCharacters, mt_rand(0, strlen($this->strAllowedCharacters) - 1), 1);
      }//for

      $this->setCode($strCode);
    }//function

    public function setAllowedCharacters ($strAllowedCharacters) {
      $this->strAllowedCharacters = $strAllowedCharacters;
      $this->generateNewCode();
    }//if

    public function setCodeLength ($intCodeLength) {
      $this->intLength = $intCodeLength;
      $this->generateNewCode();
    }//if

    public function outputImage () {
      //Load resources
      $this->loadFonts();
      $this->loadBackgrounds();

      //Initialise Image & Colors
      $this->setupImage();
      $this->generateRandomColors();

      //Add Code
      $this->addCodeToImage();

      //Distort Letters
      $this->distortImage();

      //Background
      $this->applyRandomBackground();

      //Noise & Lines
      $this->addLines();
      $this->addNoise();

      //Add Border & Output
      $this->addImageBorder();
      $this->outputStoredImage();
    }//function

    public function getImageWidth () {
      return $this->intImageWidth;
    }//function

    public function getImageHeight () {
      return $this->intImageHeight;
    }//function

    /*********************/
    /* Private Functions */
    /*********************/

    private function loadConfig () {
      $objCaptchaConfig = Config::get('captcha');

      $this->strAllowedCharacters          =       $objCaptchaConfig->allowedcharacters;
      $this->intLength                     = (int) $objCaptchaConfig->captchalength;
      $this->strSessionName                =       $objCaptchaConfig->sessionname;
      $this->blnCaseSensitiveComparison    =      ($objCaptchaConfig->casesensitive == 'true');
      $this->intImageWidth                 = (int) $objCaptchaConfig->image->width;
      $this->intImageHeight                = (int) $objCaptchaConfig->image->height;
      $this->intPaddingLeft                = (int) $objCaptchaConfig->spacing->paddingleft;
      $this->intLetterPadding              = (int) $objCaptchaConfig->spacing->letterpadding;
      $this->intHeightVariation            = (int) $objCaptchaConfig->spacing->heightvariation;
      $this->intFontTilt                   = (int) $objCaptchaConfig->text->fonttilt;
      $this->intFontSize                   = (int) $objCaptchaConfig->text->fontsize;
      $this->strFontDirectory              =       Application::getBasePath() . "lib/res/" . $objCaptchaConfig->text->fontdirectory;
      $this->strBackgroundImagesDirectory  =       Application::getBasePath() . "lib/res/" . $objCaptchaConfig->image->backgroundimagesdirectory;
      $this->intDarkestColor               = (int) $objCaptchaConfig->color->darkestcolor;
      $this->intLightestColor              = (int) $objCaptchaConfig->color->lightestcolor;
      $this->intNumberOfColors             = (int) $objCaptchaConfig->color->numberofcolors;
      $this->intNoiseAmount                = (int) $objCaptchaConfig->noise->noiseamount;
      $this->intLines                      = (int) $objCaptchaConfig->noise->numberoflines;
      $this->intLineThickness              = (int) $objCaptchaConfig->noise->linethickness;
      $this->intHorizontalLinesProbability = (int) $objCaptchaConfig->noise->horizontallinesprobability;
    }//function

    private function setCode ($strCode) {
      $this->strCode = $strCode;
      $_SESSION[$this->strSessionName] = $this->strCode;
    }//if

    private function loadFonts () {
      $this->arrFonts = $this->getFilesInDirectory($this->strFontDirectory, array('.svn'));
    }//function

    private function loadBackgrounds () {
      $arrBackgrounds = $this->getFilesInDirectory($this->strBackgroundImagesDirectory, array('.svn'));

      $arrBackgroundsForLooping = $arrBackgrounds;

      foreach ($arrBackgroundsForLooping as $intKey => $strBackgroundImageFilename) {
        list ($intWidth, $intHeight) = getimagesize($strBackgroundImageFilename);

        if (($intWidth != $this->intImageWidth) || ($intHeight != $this->intImageHeight)) {
          unset ($arrBackgrounds[$intKey]);
        }//if
      }//foreach

      foreach ($arrBackgrounds as $strBackgroundImageFilename) {
        $this->arrBackgrounds[] = $strBackgroundImageFilename;
      }//foreach
    }//function

    private function setupImage () {
      $this->imgCaptchaImage = imagecreatetruecolor($this->intImageWidth, $this->intImageHeight);
      imagealphablending($this->imgCaptchaImage, true);

      $this->arrPresetColors['transparent'] = imagecolorallocatealpha($this->imgCaptchaImage, 255, 255, 255, 127);
      $this->arrPresetColors['white'] = imagecolorallocate($this->imgCaptchaImage, 255, 255, 255);
      $this->arrPresetColors['black'] = imagecolorallocate($this->imgCaptchaImage, 0, 0, 0);

      imagefill($this->imgCaptchaImage, 0, 0, $this->arrPresetColors['transparent']);
    }//function

    private function generateRandomColors () {
      for ($intCounter = 0; $intCounter < $this->intNumberOfColors; $intCounter++) {
        $this->arrRandomColors[$intCounter] = imagecolorallocate($this->imgCaptchaImage, mt_rand($this->intDarkestColor, $this->intLightestColor), mt_rand($this->intDarkestColor, $this->intLightestColor), mt_rand($this->intDarkestColor, $this->intLightestColor));
      }//for
    }//function

    private function addCodeToImage () {
      $strCode = $this->getCode();
      $intLeft = $this->intPaddingLeft;

      for ($i = 0; $i < strlen($strCode); $i++) {
        $strFontFile = $this->arrFonts[mt_rand(0, count($this->arrFonts) - 1)];
        $strLetter = substr($strCode, $i, 1);
        $intLetterTilt = mt_rand(0 - ($this->intFontTilt / 2), $this->intFontTilt / 2);

        $arrTextSize = imagettfbbox($this->intFontSize, $intLetterTilt, $strFontFile, $strLetter);

        $intLetterLeftBound = 0 - (min($arrTextSize[0], $arrTextSize[2], $arrTextSize[4], $arrTextSize[6]));
        $intLetterRightBound = max($arrTextSize[0], $arrTextSize[2], $arrTextSize[4], $arrTextSize[6]);

        imagettftext($this->imgCaptchaImage, $this->intFontSize, $intLetterTilt, $intLeft + $intLetterLeftBound, $this->intImageHeight - (($this->intImageHeight - $this->intFontSize) / 2) + mt_rand(0 - ($this->intHeightVariation / 2), ($this->intHeightVariation / 2)), $this->arrRandomColors[mt_rand(0, count($this->arrRandomColors) - 1)], $strFontFile, $strLetter);
        $intLeft += $intLetterLeftBound + $intLetterRightBound + $this->intLetterPadding;
      }//for
    }//function

    private function distortImage () {
      $arrDistortionValues = array();

      $intMultiplier1 = mt_rand(1, 20);
      $intMultiplier2 = mt_rand(1, 20);
      $intMultiplier3 = mt_rand(1, 20);

      for ($i = 0; $i <= $this->intImageHeight; $i++) {
        $arrDistortionValues[$i] = round(5 * (sin($i/11 + $intMultiplier1/10) + sin($i/13 + $intMultiplier2/10) + sin($i/17 - $intMultiplier3/10)));
      }//for

      //This section distorts the image by applying the array generated above
      $imgNewImage = imagecreatetruecolor($this->intImageWidth, $this->intImageHeight);
      imagealphablending($imgNewImage, true);
      imagefill($imgNewImage, 0, 0, imagecolorallocatealpha($imgNewImage, 255, 255, 255, 127));

      for ($i = 0; $i < $this->intImageHeight; $i++) {
        for ($j = 0; $j < $this->intImageWidth; $j++) {
          if ((($j + $arrDistortionValues[$i]) >= 0) && (($j + $arrDistortionValues[$i]) < $this->intImageWidth)) {
            imagesetpixel($imgNewImage, $j, $i, imagecolorat($this->imgCaptchaImage, $j + $arrDistortionValues[$i], $i));
          }//if
        }//for
      }//for

      $this->imgCaptchaImage = $imgNewImage;
    }//function

    private function applyRandomBackground () {
      if (count($this->arrBackgrounds) > 0) {
        $strBackgroundFile = $this->arrBackgrounds[mt_rand(0, count($this->arrBackgrounds) - 1)];

        $imgBackground = imagecreatefromjpeg($strBackgroundFile);

        imagecopy($imgBackground, $this->imgCaptchaImage, 0, 0, 0, 0, $this->intImageWidth, $this->intImageHeight);

        $this->imgCaptchaImage = $imgBackground;
      }//if
    }//function

    private function addLines () {
      for ($i = 0; $i < $this->intLines; $i++) {
        switch (mt_rand(-1, $this->intHorizontalLinesProbability)) {
          case -1:
            $this->imageLineThick(mt_rand(0, $this->intImageWidth / 3), mt_rand(0, ($this->intImageHeight / 3)), mt_rand(2 * ($this->intImageWidth / 3), $this->intImageWidth), mt_rand(2 * ($this->intImageHeight / 3), $this->intImageHeight), $this->arrRandomColors[mt_rand(0, count($this->arrRandomColors) - 1)]);
            break;

          case 0:
            $this->imageLineThick(mt_rand(0, $this->intImageWidth / 3), mt_rand(2 * ($this->intImageHeight / 3), $this->intImageHeight), mt_rand(2 * ($this->intImageWidth / 3), $this->intImageWidth), mt_rand(0, ($this->intImageHeight / 3)), $this->arrRandomColors[mt_rand(0, count($this->arrRandomColors) - 1)]);
            break;

          default:
            $this->imageLineThick(mt_rand(0, $this->intImageWidth / 3), mt_rand(($this->intImageHeight / 3), 2 * ($this->intImageHeight / 3)), mt_rand(2 * ($this->intImageWidth / 3), $this->intImageWidth), mt_rand(($this->intImageHeight / 3), 2 * ($this->intImageHeight / 3)), $this->arrRandomColors[mt_rand(0, count($this->arrRandomColors) - 1)]);
            break;
        }//switch
      }//for
    }//function

    private function addNoise () {
      for ($i = 0; $i <= $this->intNoiseAmount; $i++) {
        imagesetpixel($this->imgCaptchaImage, mt_rand(0, $this->intImageWidth), mt_rand(0, $this->intImageHeight), $this->arrRandomColors[mt_rand(0, count($this->arrRandomColors) - 1)]);
      }//for
    }//function

    private function addImageBorder () {
      imageline($this->imgCaptchaImage, 0, 0, 0, ($this->intImageHeight - 1), $this->arrPresetColors['black']);
      imageline($this->imgCaptchaImage, 0, 0, ($this->intImageWidth - 1), 0, $this->arrPresetColors['black']);
      imageline($this->imgCaptchaImage, 0, ($this->intImageHeight - 1), ($this->intImageWidth - 1), ($this->intImageHeight - 1), $this->arrPresetColors['black']);
      imageline($this->imgCaptchaImage, ($this->intImageWidth - 1), 0, ($this->intImageWidth - 1), ($this->intImageHeight - 1), $this->arrPresetColors['black']);
    }//function

    private function outputStoredImage () {
      header('Content-type: image/jpeg');
      imagejpeg($this->imgCaptchaImage);
    }//function

    /*********************/
    /* Utility Functions */
    /*********************/

    private function imageLineThick ($intX1, $intY1, $intX2, $intY2, $colColor) {
      //Function from comments at http://www.php.net/imageline, and modified for use with this class

      if ($this->intLineThickness == 1) {
        return imageline($this->imgCaptchaImage, $intX1, $intY1, $intX2, $intY2, $colColor);
      }//if

      $t = $this->intLineThickness / 2 - 0.5;

      if ($intX1 == $intX2 || $intY1 == $intY2) {
        return imagefilledrectangle($this->imgCaptchaImage, round(min($intX1, $intX2) - $t), round(min($intY1, $intY2) - $t), round(max($intX1, $intX2) + $t), round(max($intY1, $intY2) + $t), $colColor);
      }//if

      $k = ($intY2 - $intY1) / ($intX2 - $intX1);
      $a = $t / sqrt(1 + pow($k, 2));

      $arrPolygonPoints = array(round($intX1 - (1 + $k) * $a), round($intY1 + (1 - $k) * $a),
                               round($intX1 - (1 - $k) * $a), round($intY1 - (1 + $k) * $a),
                               round($intX2 + (1 + $k) * $a), round($intY2 - (1 - $k) * $a),
                               round($intX2 + (1 - $k) * $a), round($intY2 + (1 + $k) * $a));

      imagefilledpolygon($this->imgCaptchaImage, $arrPolygonPoints, 4, $colColor);
      return imagepolygon($this->imgCaptchaImage, $arrPolygonPoints, 4, $colColor);
    }//function

    private function getFilesInDirectory ($strDirectoryName, $arrExceptions = array()) {
    $arrFiles = array();

    $dirDirectoryHandle = opendir($strDirectoryName);

    while (false !== ($strFilename = readdir($dirDirectoryHandle))) {
      if (($strFilename != '.') && ($strFilename != '..') && !in_array($strFilename, $arrExceptions)) {
        $arrFiles[] = $strDirectoryName . $strFilename;
      }//if
    }//while

    return $arrFiles;
  }//function
  }//class
?>