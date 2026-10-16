<?php
\defined('_JEXEC') or die;
/** Page layout picker for a document section. Expects $this->layoutCtx = ['name' => 'pricing'|'terms', 'label' => string, 'break' => int, 'space' => int]. */
$c = $this->layoutCtx;
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<div class="sp-section-layout" data-layout-pick>
 <span class="sp-field-label"><?php echo $e($c['label']); ?> στο Pdf</span>
 <div class="sp-oi-layout">
  <select name="<?php echo $e($c['name']); ?>_break" aria-label="<?php echo $e($c['label']); ?>: θέση στο Pdf">
   <option value="0"<?php echo (int) $c['break'] ? '' : ' selected'; ?>>Συνέχεια στην ίδια σελίδα</option>
   <option value="1"<?php echo (int) $c['break'] ? ' selected' : ''; ?>>Ξεκινά σε νέα σελίδα</option>
  </select>
  <label class="sp-oi-space" data-space<?php echo (int) $c['break'] ? ' hidden' : ''; ?>>απόσταση <input type="number" min="0" max="80" step="1" name="<?php echo $e($c['name']); ?>_space" value="<?php echo (int) $c['space']; ?>" aria-label="Απόσταση από την προηγούμενη ενότητα σε mm"> mm</label>
 </div>
</div>
