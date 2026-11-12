<?php
namespace Support\Component\Supportplans\Site\Controller;
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class SettingsController extends BaseController
{
 public function apply():void{$this->store(false);}
 public function save():void{$this->store(true);}
 public function cancel():void{Factory::getApplication()->redirect(Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&Itemid=' . max(1,(int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid',785)) . '',false));}

 /** Photo of the signed-in user (bottom of the sidebar): upload a new one or remove it. */
 public function avatar():void
 {
  Session::checkToken() or jexit('Invalid Token');
  $app=Factory::getApplication();
  $uid=(int)$app->getSession()->get('supportplans.auth.user_id',0);
  $back=\Support\Component\Supportplans\Site\Helper\OfferHelper::url('view=settings',false).'#sp-my-account';
  if($uid<1){$app->redirect($back);return;}
  $key='avatar_'.$uid;
  $old=trim((string)ComponentHelper::getParams('com_supportplans')->get($key,''));
  $removeOld=static function() use ($old){ if($old!=='' && preg_match('#^images/supportplans/avatars/[a-z0-9_.-]+$#i',$old) && is_file(JPATH_ROOT.'/'.$old)) @unlink(JPATH_ROOT.'/'.$old); };
  if($app->input->getInt('remove',0)===1){
   $removeOld();
   \Support\Component\Supportplans\Site\Helper\OfferHelper::saveParam($key,'');
   $app->enqueueMessage('Η φωτογραφία αφαιρέθηκε.');
   $app->redirect($back);return;
  }
  $up=$app->input->files->get('avatar',null,'array');
  if(!is_array($up) || (int)($up['error'] ?? UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK || !is_uploaded_file((string)$up['tmp_name'])){
   $app->enqueueMessage('Επιλέξτε μια φωτογραφία.','error');$app->redirect($back);return;
  }
  // Only real images (no SVG), up to 2 MB.
  $allowed=['image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp'];
  $info=@getimagesize((string)$up['tmp_name']);
  $mime=is_array($info)?(string)$info['mime']:'';
  if(!isset($allowed[$mime]) || (int)$up['size']>2*1024*1024){
   $app->enqueueMessage('Επιτρέπονται εικόνες PNG, JPG ή WEBP έως 2 MB.','error');$app->redirect($back);return;
  }
  $dir=JPATH_ROOT.'/images/supportplans/avatars';
  if(!Folder::exists($dir))Folder::create($dir);
  $file='user-'.$uid.'-'.substr(bin2hex(random_bytes(6)),0,10).'.'.$allowed[$mime];
  if(!File::upload((string)$up['tmp_name'],$dir.'/'.$file)){
   $app->enqueueMessage('Η φωτογραφία δεν αποθηκεύτηκε.','error');$app->redirect($back);return;
  }
  $removeOld();
  \Support\Component\Supportplans\Site\Helper\OfferHelper::saveParam($key,'images/supportplans/avatars/'.$file);
  $app->enqueueMessage('Η φωτογραφία αποθηκεύτηκε.');
  $app->redirect($back);
 }

 private function store(bool $close):void
 {
  Session::checkToken() or jexit('Invalid Token');
  $app=Factory::getApplication();$in=$app->input;$db=Factory::getContainer()->get('DatabaseDriver');

  $q=$db->getQuery(true)->select(['extension_id','params'])->from($db->quoteName('#__extensions'))
    ->where($db->quoteName('type').'='.$db->quote('component'))
    ->where($db->quoteName('element').'='.$db->quote('com_supportplans'));
  $row=$db->setQuery($q)->loadObject();
  if(!$row){$app->enqueueMessage('Δεν βρέθηκε η εγγραφή του component.','error');$app->redirect(Route::_('index.php?option=com_supportplans&view=settings&tmpl=component&Itemid=' . max(1,(int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid',785)) . '',false));return;}

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
  // Sidebar logo (v1.21.0): path/URL or upload; optional white (monochrome) display.
  $params['sidebar_logo']=trim((string)$in->get('sidebar_logo','','string'));
  $params['sidebar_logo_white']=(int)$in->get('sidebar_logo_white',0,'int')?1:0;
  $sbUpload=$in->files->get('sidebar_logo_upload',null,'array');
  if(is_array($sbUpload) && !empty($sbUpload['name']) && (int)($sbUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE){
   $allowed=['image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp','image/gif'=>'gif','image/svg+xml'=>'svg'];
   $tmp=(string)$sbUpload['tmp_name'];
   $mime='';
   if(function_exists('finfo_open')){$fi=finfo_open(FILEINFO_MIME_TYPE);if($fi){$mime=(string)finfo_file($fi,$tmp);finfo_close($fi);}}
   if((int)$sbUpload['error'] !== UPLOAD_ERR_OK){
    $app->enqueueMessage('Η μεταφόρτωση του λογοτύπου sidebar απέτυχε.','error');
   }elseif(!isset($allowed[$mime]) || (int)$sbUpload['size'] > 2*1024*1024){
    $app->enqueueMessage('Λογότυπο sidebar: επιτρέπονται PNG, JPG, WEBP, GIF ή SVG έως 2 MB.','error');
   }else{
    $dir=JPATH_ROOT.'/images/supportplans';
    if(!Folder::exists($dir))Folder::create($dir);
    $filename='sidebar-logo.'.$allowed[$mime];
    foreach(array_unique(array_values($allowed)) as $ext){$old=$dir.'/sidebar-logo.'.$ext;if($old!==$dir.'/'.$filename && File::exists($old))File::delete($old);}
    if(File::upload($tmp,$dir.'/'.$filename,true)){
     $params['sidebar_logo']='images/supportplans/'.$filename;
    }else{
     $app->enqueueMessage('Δεν ήταν δυνατή η αποθήκευση του λογοτύπου sidebar στον φάκελο images/supportplans.','error');
    }
   }
  }
  $params['pdf_watermark']=trim((string)$in->get('pdf_watermark','','string'));
  $params['watermark_opacity']=max(0,min(100,(int)$in->get('watermark_opacity',7,'int')));
  $params['supported_websites_label']=trim((string)$in->get('supported_websites_label','Υποστηριζόμενα websites','string'));
  $params['standalone_itemid']=max(0,(int)$in->get('standalone_itemid',785,'int'));

  $params['login_icon']=trim((string)$in->get('login_icon','','string'));
  $params['page_title']=trim((string)$in->get('page_title','','string'));
  $params['login_lifetime']=max(0,min(24,(int)$in->get('login_lifetime',12,'int')));
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
  // Signature / stamp image shown at the end of offer PDFs.
  $params['offer_signature']=trim((string)$in->get('offer_signature','','string'));
  $signature=$in->files->get('offer_signature_upload',null,'array');
  if(is_array($signature) && !empty($signature['name']) && (int)($signature['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE){
   $ext=strtolower(pathinfo((string)$signature['name'],PATHINFO_EXTENSION));
   $ext=$ext==='jpeg'?'jpg':$ext;
   if((int)$signature['error'] !== UPLOAD_ERR_OK || empty($signature['tmp_name'])){
    $app->enqueueMessage('Η μεταφόρτωση της υπογραφής απέτυχε.','error');
   }elseif(!in_array($ext,['png','jpg','webp','gif','svg'],true)){
    $app->enqueueMessage('Η υπογραφή πρέπει να είναι PNG, JPG, WEBP, GIF ή SVG.','error');
   }else{
    $dir=JPATH_ROOT.'/images/supportplans';
    if(!Folder::exists($dir))Folder::create($dir);
    foreach(['png','jpg','webp','gif','svg'] as $oldExt){$old=$dir.'/offer-signature.'.$oldExt;if($oldExt!==$ext && File::exists($old))File::delete($old);}
    if(File::upload((string)$signature['tmp_name'],$dir.'/offer-signature.'.$ext,false,true)){
     $params['offer_signature']='images/supportplans/offer-signature.'.$ext;
    }else{
     $app->enqueueMessage('Δεν ήταν δυνατή η αποθήκευση της υπογραφής στον φάκελο images/supportplans.','error');
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
  $app->redirect(Route::_($close?'index.php?option=com_supportplans&view=plans&tmpl=component&Itemid=' . max(1,(int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid',785)) . '':'index.php?option=com_supportplans&view=settings&tmpl=component&Itemid=' . max(1,(int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid',785)) . '',false));
 }
}
