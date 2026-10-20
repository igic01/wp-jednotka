<?php
/** Standalone featured article checks; WordPress post APIs are stubbed. */
namespace Elementor {
 class Controls_Manager { const ICONS='icons',TEXT='text',TEXTAREA='textarea',COLOR='color',RAW_HTML='raw',GALLERY='gallery',MEDIA='media',SELECT='select',REPEATER='repeater',SWITCHER='switcher',NUMBER='number',URL='url',TAB_STYLE='style',SLIDER='slider',DIMENSIONS='dimensions'; }
 class Group_Control_Typography { static function get_type(){ return 'typography'; } }
 class Icons_Manager { static function render_icon($icon,$attrs){echo '<i class="'.\esc_attr($icon['value']).'" aria-hidden="true"></i>';} }
 class Plugin { public static $instance; }
 class EditorStub { public $editing=false; function is_edit_mode(){return $this->editing;} }
 class Repeater {
  protected $controls=[]; function add_control($key,$args){if(isset($this->controls[$key]))throw new \RuntimeException("Duplicate Elementor control: ".$key);$this->controls[$key]=$args;} function get_controls(){return $this->controls;} function add_responsive_control($key,$args){$this->add_control($key,$args);} function add_group_control($type,$args){}
 }
 class Widget_Base extends Repeater {

  function __construct(){ $this->register_controls(); }
  function start_controls_section($id,$args){$this->add_control($id,array_merge($args,['type'=>'section']));} function end_controls_section(){}
  function add_control($key,$args){if(isset($this->controls[$key]))throw new \RuntimeException("Duplicate Elementor control: ".$key);$this->controls[$key]=$args;}
  function add_responsive_control($key,$args){$this->add_control($key,$args);}
  function add_group_control($type,$args){}
  public $overrides=[];
  function get_settings_for_display(){ $s=[]; foreach($this->controls as $k=>$a){$s[$k]=$a['default']??'';if(($a['type']??'')==='repeater'){$d=[];foreach($a['fields'] as $key=>$f)$d[$key]=$f['default']??'';foreach($s[$k] as $i=>&$item)$item=array_merge($d,$item,['_id'=>'item'.$i]);unset($item);}} return array_replace($s,$this->overrides); }
  private $attrs=[];
  function update_control($key,$args){$this->controls[$key]=array_replace($this->controls[$key],$args);}
  function add_link_attributes($key,$args){if(!empty($args['url']))$this->add_render_attribute($key,'href',$args['url']);}
  function add_render_attribute($key,$name,$value=null){if(is_array($name)){foreach($name as $k=>$v)$this->add_render_attribute($key,$k,$v);return;}$this->attrs[$key][$name][]=$value;}
  function print_render_attribute_string($key){foreach($this->attrs[$key]??[] as $name=>$values)echo $name.'="'.\esc_attr(implode(' ',$values)).'" ';}
  function add_inline_editing_attributes($key,$mode){$this->add_render_attribute($key,'data-elementor-setting-key',$key);$this->add_render_attribute($key,'class','elementor-inline-editing');}
  function get_repeater_setting_key($field,$key,$index){return "$key.$index.$field";}
  function get_id(){return 'preview';}
  function output(){ob_start();$this->render();return ob_get_clean();}
 }
}
namespace {
 define('ABSPATH',__DIR__);define('JEDNOTKA_ELEMENTOR_URL','/');
 function esc_html($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');} function esc_attr($v){return esc_html($v);} function esc_url($v){return esc_html($v);} function esc_html__($v,$d){return $v;}
 function home_url($path){return $path;} function esc_attr__($s,$d){return esc_attr($s);}
 function admin_url($path){return '/'.$path;} function wp_create_nonce($action){return 'preview-only';}
 function sanitize_title($s){$v=iconv('UTF-8','ASCII//TRANSLIT',$s);return trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/','-',$v)),'-');}
 function wp_get_attachment_image_url($id,$size){return 'https://example.com/media-'.$id.'.jpg';}
 function untrailingslashit($s){return rtrim($s,'/');}
 function get_posts($args){$GLOBALS['query']=$args;return $GLOBALS['posts']??[];}
 function get_the_title($post){return $post->post_title;}
 function strip_shortcodes($text){return preg_replace('/\[[^\]]+\]/','',$text);}
 function wp_strip_all_tags($text){return strip_tags($text);}
 function wp_trim_words($text,$limit,$suffix){$words=preg_split('/\s+/',trim($text));return implode(' ',array_slice($words,0,$limit)).(count($words)>$limit?$suffix:'');}
 function get_the_category($id){return [(object)['name'=>'Web']];}
 function get_the_date($format,$post){return '17. október 2026';}
 function get_permalink($post){return 'https://example.com/post/'.$post->ID;}
 function get_the_post_thumbnail_url($post,$size){return $post->thumbnail??false;}
 \Elementor\Plugin::$instance=(object)['editor'=>new \Elementor\EditorStub];

 function wp_kses($text,$allowed){return strip_tags($text,'<br>');}
 require dirname(__DIR__).'/widgets/class-clients-footer-widget.php';
 require dirname(__DIR__).'/widgets/class-footer-widget.php';
 require dirname(__DIR__).'/widgets/class-header-widget.php';
 function check($yes,$message){if(!$yes)throw new \RuntimeException($message);}
 $footer=new \Jednotka\Elementor\Widgets\Footer_Widget;$html=$footer->output();
 check(str_contains($html,'<footer class="site-footer-card"'),'footer landmark');
 check(str_contains($html,'jn-footer-only'),'standalone wrapper');
 check(!str_contains($html,'jn-cta-stage')&&!str_contains($html,'floating-project-card')&&!str_contains($html,'cursor-trail-layer'),'no CTA or floating effects');
 check(str_contains($html,'mailto:hello@jednotka.sk')&&str_contains($html,'tel:+421000000000'),'contact links');
 check(str_contains($html,'data-elementor-setting-key="brand_text"'),'editable brand');
 check(str_contains($html,'data-elementor-setting-key="menu_links.0.label"'),'editable navigation');
 $header=new \Jednotka\Elementor\Widgets\Header_Widget;
 $header_links=$header->get_settings_for_display()['nav_items'];
 $footer_links=$footer->get_settings_for_display()['menu_links'];
 foreach($header_links as $index=>$link){check($footer_links[$index]['label']===$link['label'] && $footer_links[$index]['link']===$link['link'],'footer defaults include header links in the same order');}
 check($header_links[3]['link']['url']==='/kariera/','career uses a page URL');
 foreach(['instagram','behance','linkedin-in'] as $brand)check(str_contains($html,'fab fa-'.$brand),'Font Awesome brand '.$brand);
 check(str_contains($html,'aria-label="Instagram"'),'icon link has an accessible name');
 check(in_array('elementor-icons-fa-brands',$footer->get_style_depends(),true),'brand font stylesheet is loaded');
 $legacy=new \Jednotka\Elementor\Widgets\Footer_Widget;
 $legacy->overrides=['social_links'=>[['_id'=>'old','label'=>'Ig','accessible_name'=>'Instagram','link'=>['url'=>'https://example.com/social']]]];
 check(str_contains($legacy->output(),'fab fa-instagram'),'old saved social links receive icons');

 check(!isset($footer->get_controls()['cards'])&&!isset($footer->get_controls()['heading']),'only footer controls');
 check(isset($footer->get_controls()['newsletter_show']),'newsletter setting retained');
 $footer=new \Jednotka\Elementor\Widgets\Footer_Widget;$footer->overrides=['newsletter_show'=>'','brand_text'=>'<script>Brand</script>'];$safe=$footer->output();check(!str_contains($safe,'<form'),'newsletter can be hidden');check(str_contains($safe,'&lt;script&gt;Brand&lt;/script&gt;'),'brand escaped');
 $original=new \Jednotka\Elementor\Widgets\Clients_Footer_Widget;$combined=$original->output();check(str_contains($combined,'jn-cta-stage')&&str_contains($combined,'<footer class="site-footer-card"'),'original retains CTA and shared footer');check(substr_count($combined,'class="floating-project-card ')===6,'original retains floating cards');
 echo "Footer render checks passed (standalone controls, markup, inline editing, original widget).\n";
}
