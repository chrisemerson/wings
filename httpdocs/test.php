<?php
  require_once dirname(__FILE__) . "/../app/inc.framework.php";

  class Test extends BaseController {
    public function run () {
      $this->input->addValidationRules(array('firstname' => array('label' => 'First Name',
                                                                  'rules' => array('required',
                                                                                   'length_min[6]',
                                                                                   'callback[validatefield]',
                                                                                   'regex[/^[A-Z]*$/i]')),
                                             'surname' => array('label' => 'Surname',
                                                                'rules' => array('required',
                                                                                 'length_max[12]')),
                                             'password' => array('label' => 'Password',
                                                                 'rules' => array('required',
                                                                                  'length[8]')),
                                             'repeatpassword' => array('label' => 'Repeat Password',
                                                                       'rules' => array('required',
                                                                                        'matches[password]')),
                                             'email' => array('label' => 'Email Address',
                                                              'rules' => array('required',
                                                                               'valid_email',
                                                                               'unique[User, user_email]'))));

      if ($this->input->validate()) {
        echo "Thank you for submitting form";
      } else {
        if ($this->input->isError()) {
          $objFormErrors = $this->input->getErrorRegistry();
          $arrErrors = $objFormErrors->getErrors();

          echo "<ul>\n";

          foreach ($arrErrors as $strError) {
            echo "  <li>" . $strError . "</li>\n";
          }//foreach

          echo "</ul>\n";
        }//if
    ?>

    <form action="test.php" method="post">
      <label for="firstname">First Name</label>
      <input type="text" id="firstname" name="firstname" value="<?php echo $this->input->getValue('firstname'); ?>"><br>

      <label for="surname">Surname</label>
      <input type="text" id="surname" name="surname" value="<?php echo $this->input->getValue('surname'); ?>"></br>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" value="<?php echo $this->input->getValue('password'); ?>"></br>

      <label for="repeatpassword">Repeat Password</label>
      <input type="password" id="repeatpassword" name="repeatpassword" value="<?php echo $this->input->getValue('repeatpassword'); ?>"></br>

      <label for="email">Email Address</label>
      <input type="text" id="email" name="email" value="<?php echo $this->input->getValue('email'); ?>"></br>

      <input type="submit" name="submit" value="Submit">
    </form>
<?php
      }//
    }//function

    public function validatefield ($strValue) {
      return true;
    }//function
  }//class

  $objTest = new Test();
  $objTest->run();