<?php
  function showException ($exException) {
    echo "<h1>Uncaught " . get_class($exException) . "</h1>\n\n";
    echo "<p><b>" . $exException->getFile() . "(" . $exException->getLine() . ")</b></p>";

    $arrTrace = $exException->getTrace();

    echo "<ol>";

    foreach ($arrTrace as $arrTraceStep) {
      echo "<li>\n";
      echo "  <dl>\n";
      echo "    <dt><b>File (Line)</b></dt>\n";
      echo "    <dd>" . $arrTraceStep['file'] . " (" . $arrTraceStep['line'] . ")\n\n";

      echo "    <dt><b>Call</b></dt>\n";
      echo "    <dd>" . $arrTraceStep['class'] . $arrTraceStep['type'] . $arrTraceStep['function'] . "(" . implode(", ", $arrTraceStep['args']) . ")</dd>";
      echo "  </dl>\n";
      echo "</li>\n";
    }//foreach

    echo "</ol>";
  }//function