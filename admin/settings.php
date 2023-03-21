<?php
namespace TLC\Livestream\Admin;

class Settings
{
  static function init()
  {
    add_settings_section(
      'tlc-livestream-settings-section',
      'YouTube',
      '',
      'tlc-livestream-settings-page',
    );

    register_settings(
      'tlc-livestream-settings-page',
      'tlc_livestream_youtube_api_key',
      array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
      )
    );
  }

  static function menu()
  {
    add_options_page(
      'TLC Livestream Settings',
      'TLC Livestream',
      'manage_options',
      'tlc-livestream-settings-page',
      __NAMESPACE__.'\populate_settings_page',
    );
  }
}

function populate_settings_page()
{
?>
<div class="wrap">
  <h1>
    <?php echo esc_html(get_admin_page_title()); ?>
  </h1>
  <form action="options.php" method="post">
  </form>
</div>
<?php
}

