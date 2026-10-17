<?php
namespace Support\Component\Supportplans\Administrator\Controller;
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class SettingsController extends BaseController
{
 public function apply():void{$this->store(false);}
 public function save():void{$this->store(true);}
 public function cancel():void{Factory::getApplication()->redirect(Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&Itemid=785',false));}

 private function store(bool $close):void
 {
  Session::checkToken() or jexit('Invalid Token');
  $app=Factory::getApplication();$in=$app->input;$db=Factory::getContainer()->get('DatabaseDriver');

  $q=$db->getQuery(true)->select(['extension_id','params'])->from($db->quoteName('#__extensions'))
    ->where($db->quoteName('type').'='.$db->quote('component'))
    ->where($db->quoteName('element').'='.$db->quote('com_supportplans'));
  $row=$db->setQuery($q)->loadObject();
  if(!$row){$app->enqueueMessage('Δεν βρέθηκε η εγγραφή του component.','error');$app->redirect(Route::_('index.php?option=com_supportplans&view=settings&tmpl=component&Itemid=785',false));return;}

  $params=json_decode((string)$row->params,true);if(!is_array($params))$params=[];
  $params['brand_name']=trim((string)$in->get('brand_name','','string'));
  $params['brand_logo']=trim((string)$in->get('brand_logo','','string'));
  $params['brand_address']=trim((string)$in->get('brand_address','','string'));
  $params['brand_phone']=trim((string)$in->get('brand_phone','','string'));
  $params['brand_email']=trim((string)$in->get('brand_email','','string'));
  $params['brand_website']=trim((string)$in->get('brand_website','','string'));
  $params['accent_color']=trim((string)$in->get('accent_color','#367d22','string'));
  $params['secondary_color']=trim((string)$in->get('secondary_color','#245719','string'));

  // Optional logo upload from the standalone settings screen.
  $upload=$in->files->get('brand_logo_upload',null,'array');
  if(is_array($upload) && !empty($upload['name']) && (int)($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE){
   if((int)$upload['error'] !== UPLOAD_ERR_OK){
    $app->enqueueMessage('Η μεταφόρτωση του λογοτύπου απέτυχε.','error');
   }else{
    $allowed=['image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp','image/gif'=>'gif','image/svg+xml'=>'svg'];
    $tmp=(string)$upload['tmp_name'];
    $mime='';
    if(function_exists('finfo_open')){$fi=finfo_open(FILEINFO_MIME_TYPE);if($fi){$mime=(string)finfo_file($fi,$tmp);finfo_close($fi);}}
    if($mime==='' && isset($upload['type']))$mime=(string)$upload['type'];
    if(!isset($allowed[$mime])){
     $app->enqueueMessage('Επιτρέπονται μόνο PNG, JPG, WEBP, GIF ή SVG αρχεία.','error');
    }else{
     $dir=JPATH_ROOT.'/images/supportplans';
     if(!Folder::exists($dir))Folder::create($dir);
     $filename='brand-logo.'.$allowed[$mime];
     $target=$dir.'/'.$filename;
     // Remove an older logo with another supported extension.
     foreach(array_unique(array_values($allowed)) as $ext){$old=$dir.'/brand-logo.'.$ext;if($old!==$target && File::exists($old))File::delete($old);}
     if(File::upload($tmp,$target,true)){
      $params['brand_logo']='images/supportplans/'.$filename;
     }else{
      $app->enqueueMessage('Δεν ήταν δυνατή η αποθήκευση του λογοτύπου στον φάκελο images/supportplans.','error');
     }
    }
   }
  }
  $params['pdf_watermark']=trim((string)$in->get('pdf_watermark','','string'));
  $params['watermark_opacity']=max(0,min(100,(int)$in->get('watermark_opacity',7,'int')));
  $params['supported_websites_label']=trim((string)$in->get('supported_websites_label','Υποστηριζόμενα websites','string'));
  $params['standalone_itemid']=max(0,(int)$in->get('standalone_itemid',785,'int'));

  $params['login_icon']=trim((string)$in->get('login_icon','','string'));
  $params['page_title']=trim((string)$in->get('page_title','','string'));
  $params['login_lifetime']=max(0,min(2160,(int)$in->get('login_lifetime',12,'int')));
  $loginIcon=$in->files->get('login_icon_upload',null,'array');
  if(is_array($loginIcon) && !empty($loginIcon['name']) && (int)($loginIcon['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE){
   if((int)$loginIcon['error'] !== UPLOAD_ERR_OK || empty($loginIcon['tmp_name'])){
    $app->enqueueMessage('Η μεταφόρτωση του login icon απέτυχε (upload error '.(int)($loginIcon['error'] ?? -1).').','error');
   }else{
    $ext=strtolower(pathinfo((string)$loginIcon['name'],PATHINFO_EXTENSION));
    $allowed=['png','jpg','jpeg','webp','gif','svg'];
    if(!in_array($ext,$allowed,true)){
     $app->enqueueMessage('Το login icon πρέπει να είναι PNG, JPG/JPEG, WEBP, GIF ή SVG.','error');
    }else{
     $dir=JPATH_ROOT.'/images/supportplans';
     if(!Folder::exists($dir) && !Folder::create($dir)){
      $app->enqueueMessage('Δεν ήταν δυνατή η δημιουργία του φακέλου images/supportplans.','error');
     }elseif(!is_writable($dir)){
      $app->enqueueMessage('Ο φάκελος images/supportplans δεν είναι εγγράψιμος. Έλεγξε permissions/ownership.','error');
     }else{
      $targetExt=$ext==='jpeg'?'jpg':$ext;
      $filename='login-icon.'.$targetExt;
      $target=$dir.'/'.$filename;
      foreach(['png','jpg','webp','gif','svg'] as $oldExt){
       $old=$dir.'/login-icon.'.$oldExt;
       if($old!==$target && File::exists($old)){File::delete($old);}
      }
      if(File::upload((string)$loginIcon['tmp_name'],$target,false,true)){
       $params['login_icon']='images/supportplans/'.$filename;
       $app->enqueueMessage('Το login icon αποθηκεύτηκε.','message');
      }else{
       $app->enqueueMessage('Δεν ήταν δυνατή η εγγραφή του login icon. Έλεγξε permissions/ownership του images/supportplans και το Joomla Temp Path.','error');
      }
     }
    }
   }
  }
  $bankNames=(array)$in->get('bank_name',[],'array');
  $bankIbans=(array)$in->get('bank_iban',[],'array');
  $bankBeneficiaries=(array)$in->get('bank_beneficiary',[],'array');
  $bankAccounts=[];
  $bankCount=max(count($bankNames),count($bankIbans),count($bankBeneficiaries));
  for($i=0;$i<$bankCount;$i++){
   $name=trim((string)($bankNames[$i]??''));
   $iban=trim((string)($bankIbans[$i]??''));
   $beneficiary=trim((string)($bankBeneficiaries[$i]??''));
   if($name!==''||$iban!==''||$beneficiary!==''){
    $bankAccounts[]=['name'=>$name,'iban'=>$iban,'beneficiary'=>$beneficiary];
   }
  }
  $params['bank_accounts']=json_encode($bankAccounts,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  $params['report_title']=trim((string)$in->get('report_title','','string'));
  $params['report_footer']=trim((string)$in->get('report_footer','','string'));

  $q=$db->getQuery(true)->update($db->quoteName('#__extensions'))
    ->set($db->quoteName('params').'='.$db->quote(json_encode($params,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)))
    ->where($db->quoteName('extension_id').'='.(int)$row->extension_id);
  $db->setQuery($q)->execute();

  // Verify the value was really persisted before redirecting.
  $verifyQ=$db->getQuery(true)->select($db->quoteName('params'))->from($db->quoteName('#__extensions'))
    ->where($db->quoteName('extension_id').'='.(int)$row->extension_id);
  $savedParams=json_decode((string)$db->setQuery($verifyQ)->loadResult(),true);
  if(!is_array($savedParams))$savedParams=[];
  if(array_key_exists('login_icon',$params) && (string)($savedParams['login_icon'] ?? '') !== (string)$params['login_icon']){
   $app->enqueueMessage('Το Login icon ανέβηκε αλλά η διαδρομή του δεν αποθηκεύτηκε στις ρυθμίσεις του component.','error');
  }

  $app->enqueueMessage('Τα στοιχεία επιχείρησης αποθηκεύτηκαν.');
  $app->redirect(Route::_($close?'index.php?option=com_supportplans&view=plans&tmpl=component&Itemid=785':'index.php?option=com_supportplans&view=settings&tmpl=component&Itemid=785',false));
 }
}
