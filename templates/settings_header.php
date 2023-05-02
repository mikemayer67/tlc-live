<?php
namespace TLC\Live;

$title = esc_html(get_admin_page_title());

if(($_POST['action'] ?? null) == "update") {
  $status = "<span class='tlc-status'>udpated</span>";
} else {
  $status = "";
}

?>

<h1>
<?=$title?><?=$status?>
</h1>

<div class='nav-tab-wrapper'>

<?php
$tabs = [
  ['overview','Overview'],
  ['settings','Settings'],
  ['shortcode','Shortcode'],
];

foreach($tabs as $tab) {
  $class = 'nav-tab';
  $tab_key = $tab[0];
  $tab_label = $tab[1];
  if($cur_tab == $tab_key) {
    $class .= ' nav-tab-active';
  }
  $uri = "{$_SERVER['REQUEST_URI']}&tab={$tab_key}";
  echo "<a class='$class' href='$uri'>$tab_label</a>";
}
?>

</div>
