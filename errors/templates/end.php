<?php
if (!$hide_footer) {
?>
  <br>
  <font class="fonts">
  <br><br><a href=<?= $GLOBALS['jul_settings']['site_url'] ?>><?= $GLOBALS['jul_settings']['site_name'] ?></a>
<br><?= ((String)$affiliatelinks); ?>
<br>
    <table cellpadding=0 border=0 cellspacing=2><tr>
<td>
	<img class="pointresize" src=<?= $GLOBALS['jul_base_dir']; ?>/static/images/poweredbyacmlm.gif>
</td>
<td>
	<?= version_footer(); ?>
</td>
    </tr>
  </table>
<?php
}
?>
</body>
</html>
