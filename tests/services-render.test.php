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
 require dirname( __DIR__ ) . '/widgets/class-services-widget.php';
 $w=new \Jednotka\Elementor\Widgets\Services_Widget;$html=$w->output();
 function check($v,$message){if(!$v)throw new \RuntimeException($message);}
 $d=new \DOMDocument;@$d->loadHTML('<?xml encoding="UTF-8">'.$html);$x=new \DOMXPath($d);
 check($x->query('//button[@data-sv-open]')->length===6,'six service detail triggers');check($x->query('//div[@data-sv-detail and @hidden]')->length===6,'six hidden panels');
 check($x->query('//img[@loading="lazy"]')->length===216,'looping image walls');
 check(str_contains($html,'Weby, ktoré ľudia dočítajú'),'reference detail text');check(str_contains($html,'data-elementor-setting-key="services.0.title"'),'original inline editing keys');
 foreach(array('detail_heading','detail_bullets','detail_cta') as $field)check(str_contains($html,'data-elementor-setting-key="services.1.'.$field.'"'),'second service inline '.$field);
 check(str_contains($html,'data-default-heading="Weby, ktoré ľudia dočítajú"'),'inline fallback defaults');
 check($x->query('//div[contains(concat(" ",normalize-space(@class)," ")," sv-bullets-editor ") and contains(concat(" ",normalize-space(@class)," ")," elementor-inline-editing ")]')->length===6,'all benefit editors retain their inline class');
 $controls=$w->get_controls()['services']['fields'];check($controls['detail_bullets']['type']==='textarea','sidebar benefits control');check($controls['detail_images']['type']==='gallery','sidebar images control');
 $items=$w->get_settings_for_display()['services'];$items[0]['link']=['url'=>'https://example.com/custom/'];$w->overrides=['services'=>$items];$linked=$w->output();check(str_contains($linked,'href="https://example.com/custom/"'),'preserve custom links');
 $w->overrides=['services'=>[]];check(!str_contains($w->output(),'data-sv-detail='),'empty services');
 $items[1]['title']=$items[0]['title'];$w->overrides=['services'=>$items];$dup=$w->output();check(str_contains($dup,'data-sv-detail="weby-2"'),'unique duplicate service slug');
 $edited=new \Jednotka\Elementor\Widgets\Services_Widget;$items=$edited->get_settings_for_display()['services'];
 $items[2]['detail_bullets']="Edited first point\nEdited second point";$items[2]['detail_images']=[['id'=>42],['url'=>'https://example.com/custom-email.jpg']];$edited->overrides=['services'=>$items];$custom=$edited->output();
 $custom_doc=new \DOMDocument;@$custom_doc->loadHTML('<?xml encoding="UTF-8">'.$custom);$custom_x=new \DOMXPath($custom_doc);
 check($custom_x->query('//div[@data-sv-detail="emaily"]//li')->length===2,'edited bullets render for the selected service');
 check(str_contains($custom,'Edited first point'),'edited point content');
 check($custom_x->query('//div[@data-sv-detail="emaily"]//img[@src="https://example.com/media-42.jpg"]')->length>0,'ID-only gallery image resolves');
 check($custom_x->query('//div[@data-sv-detail="emaily"]//img[@src="https://example.com/custom-email.jpg"]')->length>0,'custom gallery image renders');
 check($custom_x->query('//div[@data-sv-detail="weby"]//img[@src="https://example.com/custom-email.jpg"]')->length===0,'gallery changes stay scoped to the selected service');
 echo "Services render compatibility checks passed.\n";
}
