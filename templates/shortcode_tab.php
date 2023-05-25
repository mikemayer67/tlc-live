<h2>Usage</h2>

<div class=tlc-shortcode-info>
Simply add the shortcode <code>[tlc-livestream]</code> to your pages or posts to embed
your livestream.
</div>
<div class=tlc-shortcode-note>
Only the first occurance of this shortcode on any given page will be rendered.  All others will be quietly ignored.
</div>

<div class=tlc-shortcode-info>
The following <b>optional</b> arguments are recognized:
</div>
<div class=tlc-shortcode-note>
Any unpecified argument defaults to the value defined in the plugin settings
</div>

<div class=tlc-shortcode-args>
<div class=tlc-shortcode-arg>autoplay</div>
<div class=tlc-shortcode-arg-info> 0: playback requires user interaction with the player</div>
<div class=tlc-shortcode-arg-info> 1: playback will occur without any interaction with the player</div>
<div class=tlc-shortcode-arg>contols</div>
<div class=tlc-shortcode-arg-info> 0: playback controls are <b>not</b> displayed in the player</div>
<div class=tlc-shortcode-arg-info> 1: playback controls <b>are</b> displayed in the player</div>
<div class=tlc-shortcode-arg>enabledkb</div>
<div class=tlc-shortcode-arg-info> 0: keyboard <b>cannot</b> be used to control playback</div>
<div class=tlc-shortcode-arg-info> 1: keyboard <b>can</b> be used to control playback</div>
<div class=tlc-shortcode-arg>fullscreen</div>
<div class=tlc-shortcode-arg-info> 0: the user <b>cannot</b> display the livestream using full screen</div>
<div class=tlc-shortcode-arg-info> 1: the user <b>may</b> display the livestream using full screen</div>
<div class=tlc-shortcode-arg>modestbranding</div>
<div class=tlc-shortcode-arg-info> 0: the YouTube logo may be displayed while livestreaming</div>
<div class=tlc-shortcode-arg-info> 1: the YouTube logo will be suppressed unless livestream is paused</div>
<div class=tlc-shortcode-arg>transition</div>
<div class=tlc-shortcode-arg-info> 
The number of <b>minutes</b> before the scheduled start that the player will switch from showing the 
previously recorded livestream to the upcoming livestream.
</div>
<div class=tlc-shortcode-arg>abandon</div>
<div class=tlc-shortcode-arg-info> 
The number of <b>minutes</b> after the scheduled start that a livestream will be considered "dead"
if it has not started streaming.
</div>
<div class=tlc-shortcode-arg>width</div>
<div class=tlc-shortcode-arg-info> 
The width of the player within the current page. This may be specified in any format recognized as a valid css width.
</div>
<div class=tlc-shortcode-arg-info> 
The height of the player is set using the width and the standard 16:9 YouTube aspect ratio.
</div>
</div>

<h3>Example</h3>
<div><span class=tlc-shortcode-example>
[tlc-livestream fullscreen=1 transition=3 width=75%]
</span></div>
