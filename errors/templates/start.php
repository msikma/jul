<?php

/**
 * Renders a generic header with a link back to home.
 */
function render_error_header($data) {
?>
<table class="table" cellspacing="0">
  <tr>
    <td class="tbl tdbg1 center"><a href="<?= $GLOBALS['jul_home']; ?>">Jul - <?= $data['error_header'] ? $data['error_header'] : 'An error has occurred'; ?></a></td>
  </tr>
</table>
<?php
}

?>
<html>
  <head>
    <meta http-equiv='Content-type' content='text/html; charset=utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Jul -- <?= $data['error_header'] ? $data['error_header'] : 'An error has occurred'; ?></title>
    <meta name="robots" content="noindex,follow" />
		<link rel='stylesheet' href='/jul/css/base.css' type='text/css'>
		<style type='text/css'>
		a			{	color: #BEBAFE;	}
		a:visited	{	color: #9990c0;	}
		a:active	{	color: #CFBEFF;	}
		a:hover		{	color: #CECAFE;	}
		body {
			scrollbar-face-color:		#7d7bc1;
			scrollbar-track-color:		#000020;
			scrollbar-arrow-color:		#210456;
			scrollbar-highlight-color:	#a9a7d6;
			scrollbar-3dlight-color:	#d4d3eb;
			scrollbar-shadow-color:	#524fad;
			scrollbar-darkshadow-color:	#312d7d;
			color: #DDDDDD;
			font:13px Verdana, sans-serif;
			background: #000F1F url('/jul/images/starsbg.png');
		}
		div.lastpost { font: 10px Verdana, sans-serif !important; white-space: nowrap; }
		div.lastpost:first-line { font: 13px Verdana, sans-serif !important; }
		.sparkline { display: none; }
		.brightlinks a { color: #F0C413; font-weight: normal; }
		.brightlinks a:hover { font-weight: normal; }
		.font 	{font:13px Verdana, sans-serif}
		.fonth	{font:13px Verdana, sans-serif;color:FFEEFF}	/* is this even used? */
		.fonts	{font:10px Verdana, sans-serif}
		.fontt	{font:10px Tahoma, sans-serif}
		.tdbg1	{background:#111133}
		.tdbg2	{background:#11112B}
		.tdbgc	{background:#2F2F5F}
		.tdbgh	{background:#302048; color:FFEEFF}
		.table	{empty-cells:	show; width: 100%;
				 border-top:	#000000 1px solid;
				 border-left:	#000000 1px solid;}
		td.tbl	{border-right:	#000000 1px solid;
				 border-bottom:	#000000 1px solid}
		textarea,input,select{
		  border:	#663399 solid 1px;
		  background:#000000;
		  color:	#DDDDDD;
		  font:	10pt Verdana, sans-serif;}
		textarea:focus {
		  border:	#663399 solid 1px;
		  background:#000000;
		  color:	#DDDDDD;
		  font:	10pt Verdana, sans-serif;}
		.radio{
		  border:	none;
		  background:none;
		  color:	#DDDDDD;
		  font:	10pt Verdana, sans-serif;}
		.submit{
		  border:	#663399 solid 2px;
		  font:	10pt Verdana, sans-serif;}
		</style>
  </head>
  <body>
    <center>
