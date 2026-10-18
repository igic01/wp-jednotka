<?php
/** Standalone featured article checks; WordPress post APIs are stubbed. */
namespace Elementor {
 class Controls_Manager { const TEXT='text',TEXTAREA='textarea',COLOR='color',RAW_HTML='raw',GALLERY='gallery',MEDIA='media',SELECT='select',REPEATER='repeater',SWITCHER='switcher',NUMBER='number',URL='url',TAB_STYLE='style',SLIDER='slider',DIMENSIONS='dimensions'; }
 class Group_Control_Typography { static function get_type(){ return 'typography'; } }
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

 function absint($value){return abs((int)$value);}
 function get_post_meta($id,$key,$single){return 'Media alt <'.$id.'>';}
 require dirname(__DIR__).'/widgets/class-gallery-widget.php';
 function check($value,$message){if(!$value)throw new \RuntimeException($message);}
 for($n=1;$n<=9;$n++){
  $w=new \Jednotka\Elementor\Widgets\Gallery_Widget;$images=[];for($i=1;$i<=$n;$i++)$images[]=['id'=>0,'url'=>'/photo-'.$i.'.jpg'];$w->overrides=['images'=>$images];$html=$w->output();
  check(substr_count($html,'class="jn-gallery__item"')===$n,'render count '.$n);check(str_contains($html,'jn-gallery__grid--'.$n),'layout count '.$n);check(!str_contains($html,'<figcaption'),'no visible captions');
 }
 $w=new \Jednotka\Elementor\Widgets\Gallery_Widget;$w->overrides=['images'=>array_fill(0,12,['id'=>0,'url'=>'/image.jpg'])];check(substr_count($w->output(),'class="jn-gallery__item"')===9,'limit to nine');
 $w=new \Jednotka\Elementor\Widgets\Gallery_Widget;$w->overrides=['heading'=>'<script>Heading</script>','heading_tag'=>'script','images'=>[['id'=>42,'url'=>'/ignored.jpg'],['id'=>0,'url'=>'']]];$html=$w->output();check(str_contains($html,'&lt;script&gt;Heading&lt;/script&gt;')&&str_contains($html,'<h2 '),'safe heading');check(str_contains($html,'href="https://example.com/media-42.jpg"'),'attachment full size');check(str_contains($html,'alt="Media alt &lt;42&gt;"'),'media alt escaped');check(str_contains($html,'data-elementor-setting-key="heading"'),'inline heading');check(substr_count($html,'class="jn-gallery__item"')===1,'skip empty images');
 $w=new \Jednotka\Elementor\Widgets\Gallery_Widget;$w->overrides=['images'=>[]];check(!str_contains($w->output(),'class="jn-gallery__item"'),'empty gallery');
 \Elementor\Plugin::$instance->editor->editing=true;check(str_contains($w->output(),'Vyberte obrázky'),'editor empty hint');
 echo "Gallery render checks passed (1–9 layouts, media, escaping, empty state).\n";
}
