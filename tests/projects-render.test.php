<?php
/** Standalone control/render compatibility checks; no WordPress database required. */
namespace Elementor {
 class Controls_Manager { const TEXT='text',TEXTAREA='textarea',COLOR='color',RAW_HTML='raw',GALLERY='gallery',MEDIA='media',SELECT='select',REPEATER='repeater',SWITCHER='switcher',NUMBER='number',URL='url',TAB_STYLE='style',SLIDER='slider',DIMENSIONS='dimensions'; }
 class Group_Control_Typography { static function get_type(){ return 'typography'; } }
 class Plugin { public static $instance; }
 class EditorStub { public $editing=false; function is_edit_mode(){return $this->editing;} }
 class Repeater {
  protected $controls=[]; function add_control($key,$args){$this->controls[$key]=$args;} function get_controls(){return $this->controls;} function add_responsive_control($key,$args){$this->add_control($key,$args);} function add_group_control($type,$args){}
 }
 class Widget_Base extends Repeater {

  function __construct(){ $this->register_controls(); }
  function start_controls_section($id,$args){} function end_controls_section(){}
  function add_control($key,$args){$this->controls[$key]=$args;}
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
 function get_the_title($id){return 'Project '.$id;}
 function wp_get_attachment_caption($id){return 'Caption '.$id;}
 function get_post_meta($id,$key,$single){return 'Alt <'.$id.'>';}
 require dirname(__DIR__).'/widgets/class-projects-widget.php';
 function check($value,$message){if(!$value)throw new \RuntimeException($message);}
 $widget=new \Jednotka\Elementor\Widgets\Projects_Widget;
 check($widget->get_controls()['gallery']['type']==='gallery','Elementor gallery picker');
 $legacy=$widget->output();
 check(str_contains($legacy,'Progym')&&str_contains($legacy,'gallery-1.jpg'),'original projects remain without gallery selection');
 $widget=new \Jednotka\Elementor\Widgets\Projects_Widget;
 $widget->overrides=['gallery'=>[['id'=>42],['url'=>'https://example.com/custom.jpg'],['id'=>43],[],['id'=>44],['id'=>45],['id'=>46],['id'=>47]]];
 $html=$widget->output();
 check(str_contains($html,'https://example.com/media-42.jpg'),'ID-only attachment resolves');
 check(str_contains($html,'https://example.com/custom.jpg'),'URL-only image renders');
 check(str_contains($html,'Project 42')&&str_contains($html,'Caption 42'),'attachment titles and captions');
 check(str_contains($html,'alt="Alt &lt;42&gt;"'),'escaped attachment alternative text');
 check(!str_contains($html,'Progym'),'gallery replaces sample projects');
 foreach(range(1,6) as $column)check(str_contains($html,'projects__column--'.$column),'gallery fills column '.$column);
 check(substr_count($html,'projects__image')===28,'seven valid images render with desktop/mobile loop copies');
 check(str_contains($html,'aria-hidden="true" tabindex="-1"'),'loop copies remain inaccessible');
 $widget=new \Jednotka\Elementor\Widgets\Projects_Widget;
 $widget->overrides=['gallery'=>[],'projects'=>[]];
 check(!str_contains($widget->output(),'projects__image'),'empty selections render no project images');
 echo "Projects gallery render checks passed.\n";
}
