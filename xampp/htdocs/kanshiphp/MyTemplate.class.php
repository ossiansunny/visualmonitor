<?php
class MyTemplate
{
	function show($tpl_file)
	{
		$v = $this;
		include(__DIR__ . "/templates/{$tpl_file}");
	}
}
?>