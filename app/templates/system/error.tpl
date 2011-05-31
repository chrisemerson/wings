<!-- [errortext] -->
<h2>{errortitle}</h2>

<p>{errortext}</p>
<!-- [/errortext] -->
<!-- [uncaughtexception] -->
<h2>Uncaught {exceptiontype}</h2>

<p><b>{filename} ({lineno})</b></p>

<ol>
<!-- [tracestep|tb] -->
  <li>
    <dl>
<!-- [file] -->
      <dt><b>File (Line)</b></dt>
      <dd>{filename}{[lineno| ({lineno})]}</dd>

<!-- [/file] -->
<!-- [class] -->
      <dt><b>Call</b></dt>
      <dd>{class}{type}{function}{[args|({args})]}</dd>
<!-- [/class] -->
    </dl>
  </li>

<!-- [/tracestep] -->
</ol>
<!-- [/uncaughtexception] -->