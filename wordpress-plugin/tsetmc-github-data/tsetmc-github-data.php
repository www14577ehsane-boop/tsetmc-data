<?php
/**
 * Plugin Name: TSETMC GitHub Data
 * Description: نمایش نمادها و اطلاعات TSETMC از دیتای ذخیره‌شده در GitHub.
 * Version: 3.0.0
 * Author: KAVVEX
 */
if (!defined('ABSPATH')) exit;
define('TGH_VER','3.0.0');
define('TGH_URL',plugin_dir_url(__FILE__));

class TGH_Plugin {
 const OPT='tgh_settings';
 function __construct(){
   add_action('admin_menu',[$this,'menu']);
   add_action('admin_init',[$this,'settings']);
   add_action('wp_enqueue_scripts',[$this,'assets']);
   add_shortcode('tsetmc_symbols',[$this,'shortcode']);
   add_action('rest_api_init',[$this,'routes']);
 }
 function get(){return wp_parse_args(get_option(self::OPT,[]),['json_url'=>'']);}
 function menu(){add_menu_page('TSETMC Data','TSETMC Data','manage_options','tgh-data',[$this,'page'],'dashicons-chart-area',58);}
 function settings(){register_setting('tgh_group',self::OPT,['sanitize_callback'=>function($v){return ['json_url'=>esc_url_raw(trim($v['json_url']??''))];}]);}
 function page(){
  $s=$this->get(); ?>
  <div class="wrap"><h1>TSETMC GitHub Data</h1>
  <p>آدرس فایل <code>market.json</code> ساخته‌شده توسط GitHub Actions را وارد کنید.</p>
  <form method="post" action="options.php"><?php settings_fields('tgh_group'); ?>
  <table class="form-table"><tr><th>JSON URL</th><td><input type="url" style="width:650px" name="<?php echo self::OPT;?>[json_url]" value="<?php echo esc_attr($s['json_url']);?>" placeholder="https://raw.githubusercontent.com/USERNAME/REPO/main/data/market.json"></td></tr></table>
  <?php submit_button('ذخیره'); ?></form>
  <h2>شورت‌کد</h2><code>[tsetmc_symbols]</code>
  </div><?php
 }
 function assets(){
  wp_register_style('tgh-style',TGH_URL.'assets/style.css',[],TGH_VER);
  wp_register_script('tgh-app',TGH_URL.'assets/app.js',[],TGH_VER,true);
 }
 function routes(){
  register_rest_route('tgh/v1','/market',['methods'=>'GET','permission_callback'=>'__return_true','callback'=>[$this,'market']]);
 }
 function market(){
  $s=$this->get();
  if(empty($s['json_url'])) return new WP_Error('missing','آدرس دیتای GitHub در تنظیمات وارد نشده است.',['status'=>400]);
  $cache='tgh_market_cache';
  $d=get_transient($cache);
  if($d!==false) return rest_ensure_response($d);
  $r=wp_remote_get($s['json_url'],['timeout'=>25,'headers'=>['Accept'=>'application/json']]);
  if(is_wp_error($r)) return new WP_Error('connect','خطا در دریافت داده: '.$r->get_error_message(),['status'=>502]);
  $code=wp_remote_retrieve_response_code($r);
  $body=wp_remote_retrieve_body($r);
  $d=json_decode($body,true);
  if($code!==200 || !is_array($d)) return new WP_Error('invalid','فایل JSON معتبر دریافت نشد. HTTP '.$code,['status'=>502]);
  set_transient($cache,$d,10*MINUTE_IN_SECONDS);
  return rest_ensure_response($d);
 }
 function shortcode(){
  wp_enqueue_style('tgh-style');wp_enqueue_script('tgh-app');
  wp_localize_script('tgh-app','TGH',['api'=>esc_url_raw(rest_url('tgh/v1/market'))]);
  ob_start(); ?>
  <div class="tgh-wrap" dir="rtl">
   <div class="tgh-toolbar"><input id="tgh-search" placeholder="جستجوی نماد..."></div>
   <div id="tgh-status">در حال دریافت اطلاعات...</div>
   <div class="tgh-table"><table><thead><tr><th>نماد</th></tr></thead><tbody id="tgh-body"></tbody></table></div>
   <div id="tgh-pages" class="tgh-pages"></div>
   <div id="tgh-modal" class="tgh-modal" hidden><div class="tgh-card"><button id="tgh-close">×</button><div id="tgh-detail"></div></div></div>
  </div><?php return ob_get_clean();
 }
}
new TGH_Plugin();
