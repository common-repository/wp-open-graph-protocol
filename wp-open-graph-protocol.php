<?php
/*
Plugin Name: WP Open Graph Protocol
Plugin URI: http://e-joint.jp/works/wp-open-graph-protocol/
Description: A WordPress plugin that makes Open Graph meta tag simply.
Version: 0.1.1
Author: e-JOINT.jp
Author URI: http://e-joint.jp
Text Domain: wp-open-graph-protocol
Domain Path: /languages
License: GPL2
*/

/*  Copyright 2018 e-JOINT.jp (email : mail@e-joint.jp)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Wp_Open_Graph_Protocol {

  public function __construct(){

    $this->set_datas();
    $this->options = get_option('wpogp-setting');

    // 翻訳ファイルの読み込み
    add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    // 設定画面を追加
    add_action('admin_menu', array($this, 'add_plugin_page'));
    // 設定画面の初期化
    add_action('admin_init', array($this, 'page_init'));

    add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    add_action('wp_head', array($this, 'show'));
    add_filter('language_attributes', array($this, 'language_attributes'));
  }

  private function get_option($key) {
    $option = $this->options[$key];
    $option = trim($option);
    $option = esc_html($option);

    return $option;
  }

  public function load_plugin_textdomain() {
    load_plugin_textdomain($this->textdomain, false, dirname(plugin_basename(__FILE__)) . $this->domainpath);
  }

  private function set_datas() {
    $datas = get_file_data(__FILE__, array(
      'version' => 'Version',
      'textdomain' => 'Text Domain',
      'domainpath' => 'Domain Path'
    ));

    $this->version = $datas['version'];
    $this->textdomain = $datas['textdomain'];
    $this->domainpath = $datas['domainpath'];
  }

  public function admin_enqueue_scripts() {
    wp_enqueue_media();
    wp_enqueue_script(
      'wpogp',
      plugins_url('assets/js/wp-open-graph-protocol.js', __FILE__),
      array('jquery'),
      $this->version,
      true
    );
  }

  // 設定画面を追加
  public function add_plugin_page() {

    add_options_page(
      __('WP Open Graph Protocol', $this->textdomain),
      __('WP Open Graph Protocol', $this->textdomain),
      'manage_options',
      'wpogp-setting',
      array($this, 'create_admin_page')
    );
  }

  // 設定画面を生成
  public function create_admin_page() { ?>
    <div class="wrap">
      <h2>WP Open Graph Protocol</h2>
      <?php
      global $parent_file;
      if($parent_file != 'options-general.php') {
        require(ABSPATH . 'wp-admin/options-head.php');
      }
      ?>

      <form method="post" action="options.php">
      <?php
        settings_fields('wpogp-setting');
        do_settings_sections('wpogp-setting');
        submit_button();
      ?>
      </form>
    </div>
  <?php
  }

  // 設定画面の初期化
  public function page_init(){
    register_setting('wpogp-setting', 'wpogp-setting');
    add_settings_section('wpogp-setting-section-id', '', '', 'wpogp-setting');

    add_settings_field(
      'og:locale',
      'og:locale',
      array($this, 'og_locale_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'og:type',
      'og:type',
      array($this, 'og_type_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'og:title',
      'og:title',
      array($this, 'og_title_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'og:description',
      'og:description',
      array($this, 'og_description_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'og:site_name',
      'og:site_name',
      array($this, 'og_site_name_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'og:image',
      'og:image',
      array($this, 'og_image_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'default_image_url',
      __('Default image URL', $this->textdomain),
      array($this, 'default_image_url_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'fb:admins',
      'fb:admins',
      array($this, 'fb_admins_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'fb:app_id',
      'fb:app_id',
      array($this, 'fb_app_id_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'article:publisher',
      'article:publisher',
      array($this, 'article_publisher_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'article:author',
      'article:author',
      array($this, 'article_author_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'twitter:card',
      'twitter:card',
      array($this, 'twitter_card_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );

    add_settings_field(
      'twitter:site',
      'twitter:site',
      array($this, 'twitter_site_callback'),
      'wpogp-setting',
      'wpogp-setting-section-id'
    );
  }

  public function og_locale_callback() {
    $selected = selected($this->options['og:locale'], 1, false);
    ?><select id="og:locale" name="wpogp-setting[og:locale]">
      <option value=""><?php echo __('Auto', $this->textdomain); ?></option>
      <option value="1"<?php  echo $selected; ?>><?php echo __('Not display', $this->textdomain); ?></option>
    </select><?php
  }

  public function og_type_callback() {
    $selected = selected($this->options['og:type'], 1, false);
    ?><select id="og:type" name="wpogp-setting[og:type]">
      <option value=""><?php echo __('Auto', $this->textdomain); ?></option>
      <option value="1"<?php  echo $selected; ?>><?php echo __('Not display', $this->textdomain); ?></option>
    </select><?php
  }

  public function og_title_callback() {
    $selected = selected($this->options['og:title'], 1, false);
    ?><select id="og:title" name="wpogp-setting[og:title]">
      <option value=""><?php echo __('Auto', $this->textdomain); ?></option>
      <option value="1"<?php  echo $selected; ?>><?php echo __('Not display', $this->textdomain); ?></option>
    </select><?php
  }

  public function og_description_callback() {
    $selected = selected($this->options['og:description'], 1, false);
    ?><select id="og:description" name="wpogp-setting[og:description]">
      <option value=""><?php echo __('Auto', $this->textdomain); ?></option>
      <option value="1"<?php  echo $selected; ?>><?php echo __('Not display', $this->textdomain); ?></option>
    </select><?php
  }

  public function og_site_name_callback() {
    $selected = selected($this->options['og:site_name'], 1, false);
    ?><select id="og:site_name" name="wpogp-setting[og:site_name]">
      <option value=""><?php echo __('Auto', $this->textdomain); ?></option>
      <option value="1"<?php  echo $selected; ?>><?php echo __('Not display', $this->textdomain); ?></option>
    </select><?php
  }

  public function og_image_callback() {
    $selected = selected($this->options['og:image'], 1, false);
    ?><select id="og:image" name="wpogp-setting[og:image]">
      <option value=""><?php echo __('Auto', $this->textdomain); ?></option>
      <option value="1"<?php  echo $selected; ?>><?php echo __('Not display', $this->textdomain); ?></option>
    </select><?php
  }

  public function default_image_url_callback() {
    $value = isset($this->options['default_image_url']) ? esc_html($this->options['default_image_url']) : ''; ?>
    <input type="text" id="wpogp-media-url" class="widefat" name="wpogp-setting[default_image_url]" value="<?php echo $value; ?>">
    <p>
      <button id="wpogp-media-select" class="button"><?php echo __('Select from Media Library', $this->textdomain); ?></button>
      <button id="wpogp-media-clear" class="button"><?php echo __('Clear', $this->textdomain); ?></button>
    </p>
    <p><img id="wpogp-media-image" src="<?php echo $value; ?>" style="width: 150px; height: auto;"></p>
    <?php
  }

  public function fb_admins_callback() {
    $value = esc_html($this->options['fb:admins']);
    ?><input type="text" id="fb:admins" name="wpogp-setting[fb:admins]" value="<?php echo $value; ?>"><br>
    <small><?php echo __('Please enter Facebook ID Number.', $this->textdomain); ?></small>
    <small><?php echo __('Not displayed when it is empty.', $this->textdomain); ?></small>
      <?php
  }

  public function fb_app_id_callback() {
    $value = esc_html($this->options['fb:app_id']);
    ?><input type="text" id="fb:app_id" name="wpogp-setting[fb:app_id]" value="<?php echo $value; ?>"><br>
    <small><?php echo __('Please enter Facebook App ID Number.', $this->textdomain); ?></small><br>
    <small><?php echo __('Not displayed when it is empty.', $this->textdomain); ?></small><?php
  }

  public function article_publisher_callback() {
    $value = esc_html($this->options['article:publisher']);
    ?><input type="text" id="article:publisher" name="wpogp-setting[article:publisher]" value="<?php echo $value; ?>"><br>
    <small><?php echo __('Please enter Facebook Page URL, after "https://www.facebook.com/".', $this->textdomain); ?></small><br>
    <small><?php echo __('Not displayed when it is empty.', $this->textdomain); ?></small><?php
  }

  public function article_author_callback() {
    $value = esc_html($this->options['article:author']);
    ?><input type="text" id="article:author" name="wpogp-setting[article:author]" value="<?php echo $value; ?>"><br>
    <small><?php echo __('Please enter Facebook URL, after "https://www.facebook.com/".', $this->textdomain); ?></small><br>
    <small><?php echo __('Not displayed when it is empty.', $this->textdomain); ?></small><?php
  }

  public function twitter_card_callback() {
    ?><select id="twitter:card" name="wpogp-setting[twitter:card]">
      <option value=""><?php echo __('Not display', $this->textdomain); ?></option>
      <?php $items = array('summary', 'summary_large_image');

      foreach($items as $item) {
        echo $item;
        printf('<option value="%1$s"%2$s>%1$s</option>', $item, selected($this->options['twitter_card'], $item, false));
      }?>
    </select><?php
  }

  public function twitter_site_callback() {
    $value = esc_html($this->options['twitter:site']);
    ?><input type="text" id="twitter:site" name="wpogp-setting[twitter:site]" value="<?php echo $value; ?>"><br>
    <small><?php echo __('Please enter Twitter account ID without"@"', $this->textdomain); ?></small><br>
    <small><?php echo __('Not displayed when it is empty.', $this->textdomain); ?></small><?php
  }

  public function description_callback() {
    $selected = selected($this->options['description'], 1, false);
    ?><select id="description" name="wpogp-setting[description]">
      <option value=""><?php echo __('Auto', $this->textdomain); ?></option>
      <option value="1"<?php  echo $selected; ?>><?php echo __('Not display', $this->textdomain); ?></option>
    </select><?php
  }

  public function language_attributes($output) {
    if(preg_match('/\bprefix=(["\'])([^"\']+)["\']/i', $output, $matches)) {

      $prefix = $matches[2];

      if(!preg_match('/\bog: /', $prefix)) {
        $prefix .= ' og: http://ogp.me/ns#';
      }

      if(!preg_match('/\bfb: /', $prefix)) {
        $prefix .= ' fb: http://ogp.me/ns/fb#';
      }

      $output = str_replace($matches[0], 'prefix=' . $matches[1] . $prefix.$matches[1], $output);

    } else {
      $output = $output . ' prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#"';
    }

    return $output;
  }

  public function og_locale() {
		$locale = get_bloginfo('language');

		$map = array(
			'af' => 'af_ZA',
			'ar' => 'ar_AR',
			'ary' => 'ar_AR',
			'as' => 'as_IN',
			'az' => 'az_AZ',
			'azb' => 'az_AZ',
			'bel' => 'be_BY',
			'bn_BD' => 'bn_IN',
			'bo' => 'bp_IN',
			'ca' => 'ca_ES',
			'ceb' => 'cx_PH',
			'ckb' => 'cb_IQ',
			'cy' => 'cy_GB',
			'de_CH' => 'de_DE',
			'de_CH_informal' => 'de_DE',
			'de_DE_formal' => 'de_DE',
			'el' => 'el_GR',
			'en_AU' => 'en_GB',
			'en_CA' => 'en_US',
			'en_NZ' => 'en_GB',
			'en_ZA' => 'en_GB',
			'eo' => 'eo_EO',
			'es_AR' => 'es_ES',
			'es_CL' => 'es_ES',
			'es_CO' => 'es_ES',
			'es_GT' => 'es_ES',
			'es_PE' => 'es_ES',
			'es_VE' => 'es_ES',
			'et' => 'et_EE',
			'eu' => 'eu_ES',
			'fi' => 'fi_FI',
			'fr_BE' => 'fr_FR',
			'gd' => 'ga_IE',
			'gu' => 'gu_IN',
			'hr' => 'hr_HR',
			'hy' => 'hy_AM',
			'ja' => 'ja_JP',
			'km' => 'km_KH',
			'lo' => 'lo_LA',
			'lv' => 'lv_LV',
			'mn' => 'mn_MN',
			'mr' => 'mr_IN',
			'nl_NL_formal' => 'nl_NL',
			'ps' => 'ps_AF',
			'pt_PT_ao90' => 'pt_PT',
			'sah' => 'ky_KG',
			'sq' => 'sq_AL',
			'te' => 'te_IN',
			'th' => 'th_TH',
			'tl' => 'tl_PH',
			'uk' => 'uk_UA',
			'ur' => 'ur_PK',
			'vi' => 'vi_VN',
		);

		if(isset($map[$locale])) {
      $locale = $map[$locale];
    }

		return $locale;
	}

  private function og_type($str = 'website') {
    return is_front_page() ? $str : 'article';
  }

  private function get_description(){
    global $post;
    $desc = strip_tags($post->post_content);
    $desc = trim($desc);
    $desc = str_replace("\n", ' ', $desc);
    $desc = mb_strimwidth($desc, 0, 150, '…');
    return $desc;
  }

  private function og_description(){
    return is_singular() ? $this->get_description() : get_bloginfo('description');
  }

  private function og_title(){
    return is_singular() ? get_the_title() : get_bloginfo('name');
  }

  private function og_url(){
    return is_singular() ? get_the_permalink() : home_url();
  }

  private function og_site_name(){
    return get_bloginfo('name');
  }

  private function og_image($str = ''){

    global $post;
    $content = $post->post_content;
    $searchPattern = '/<img.*?src=(["\'])(.+?)\1.*?>/i';

    if(is_singular()){
      if(has_post_thumbnail()){
        $image_id = get_post_thumbnail_id();
        $image = wp_get_attachment_image_src($image_id, 'full');
        $str = $image[0];

      } else if(preg_match($searchPattern, $content, $imgurl) && !is_archive()){
        $str = $imgurl[2];
      }
    }
    return $str;
  }

  private function facebook_url($str) {
    if($str) {
      if(preg_match('/^https:\/\/www.facebook.com/', $str)) {
        return $str;

      } else {
        return sprintf('https://www.facebook.com/%s', $str);
      }
    }
  }

  private function twitter_site($str) {
    if($str) {
      if(preg_match('/^@/', $str)) {
        return $str;

      } else {
        return sprintf('@%s', $str);
      }
    }
  }

  private function get_content($key) {
    if($key === 'og:locale') {
      $val = $this->og_locale();

    } else if($key === 'og:type') {
      $val = $this->og_type();

    } else if($key === 'og:description') {
      $val = $this->og_description();

    } else if($key === 'og:title') {
      $val = $this->og_title();

    } else if($key === 'og:url') {
      $val = $this->og_url();

    } else if($key === 'og:site_name') {
      $val = $this->og_site_name();

    } else if($key === 'og:image') {
      $val = $this->og_image($this->options['default_image_url']);

    } else if($key === 'article:author' || $key === 'article:publisher') {
      $val = $this->facebook_url($this->options[$key]);

    } else if($key === 'twitter:site') {
      $val = $this->twitter_site($this->options[$key]);

    } else {
      $val = $this->options[$key];
    }

    return $val;
  }

  private function queue() {

    $auto = array(
      'og:locale' => true,
      'og:type' => true,
      'og:title' => true,
      'og:description' => true,
      'og:site_name' => true,
      'og:image' => true,
      'fb:admins' => false,
      'fb:app_id' => false,
      'article:publisher' => false,
      'article:author' => false,
      'twitter:card' => false,
      'twitter:site' => false
    );

    $queue = array();

    foreach($auto as $key => $val) {
      if($val) {
        // Autoで表示する要素
        if(!isset($this->options[$key]) || ($this->get_option($key) === '')) {
          $queue[$key] = $this->get_content($key);
        }

      } else {
        // それ以外は入力されて入れば表示
        if($this->get_option($key) !== '') {
          $queue[$key] = $this->get_content($key);
        }
      }
    }

    return $queue;
  }

  private function tagify($property) {
    $content = $this->get_content($property);

    if($content) {
      $propname = 'property';

      if(preg_match('/^twitter:/', $property)) {
        $propname = 'name';

      } else if(preg_match('/^twitter:/', $property)) {
        $propname = 'itemprop';
      }

      return sprintf('<meta %s="%s" content="%s">%s', $propname, $property, $content, "\n");
    }

    else{
      return null;
    }
  }

  public function show() {
    $html = '';
    $html .= '<!-- Start - WP Open Graph Protocol -->' . "\n";

    foreach($this->queue() as $key => $val) {
      $html .= $this->tagify($key, $val);
    }

    $html .= '<!-- End - WP Open Graph Protocol -->' . "\n";

    echo $html;
  }
}

$wpogp = new Wp_Open_Graph_Protocol();
