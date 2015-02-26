<?php
/**
 * @package     Simple File Manager
 * @author			Giovanni Mansillo
 *
 * @copyright   Copyright (C) 2005 - 2014 Flow Solutions. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$doc = JFactory::getDocument();
$app = JFactory::getApplication();
$params	= $app->getParams();
?>

<?php if ($doc->getTitle()): ?>
	<h2 class="sfm-page-title"><?php echo $doc->getTitle(); ?></h2>
<?php endif; ?>

<?php if($this->catDesc): ?>
	<div class="sfm-cat-description">
		<?php echo $this->catDesc; ?>
	</div>
<?php endif; ?>

<div class="simplefilemanager sfm-list">
	<table class="table table-hover">
		<thead>
			<tr>
				<th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_TITLE'); ?></th>
				<th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_AUTHOR'); ?></th>
				<?php if ($app->input->get('showDate',1,"int")): ?>
					<th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_CREATED'); ?></th>
				<?php endif; ?>
				<th><?php echo JText::_('COM_SIMPLEFILEMANAGER_HEADING_FILE'); ?></th>
			</tr>
		</thead>
		<tbody>
		
		<?php foreach ($this->items as $i) : ?>
              
			<tr class="sfm-item">
				<td class="sfm-title">

					<a href="<?php echo JRoute::_('index.php?option=com_simplefilemanager&task=download&id='.(int)$i->id); ?>">
					<strong><?php echo $i->title; ?></strong>
					</a>
					<?php 
						$time = strtotime($i->file_created);
						$one_week_ago = strtotime('-1 week');
						if( $time > $one_week_ago and  $app->input->get('showNew',1,"int")):
					?>
						<span class="label label-important"><?php echo JText::_('COM_SIMPLEFILEMANAGER_NEW'); ?></span>
						<?php if($i->featured==1): ?>
							<span class="label label-warning"><?php echo JText::_('COM_SIMPLEFILEMANAGER_HOT'); ?></span>
						<?php endif; ?>
					<?php endif; ?>
					<p><?php echo $i->description; ?></p>
				</td>

				<td>
					<?php if ($i->author): ?>
						<p><?php echo JFactory::getUser($i->author)->name; ?></p>
					<?php endif; ?>
				</td>
				
				<?php if ($app->input->get('showDate',1,"int")): ?>
					<td>
						<?php echo JHTML::_('date', $i->file_created, JText::_('DATE_FORMAT_LC1')); ?></div>
					</td>
				<?php endif; ?>
					
				<?php
					if($i->icon==''){
						if($params->get('defaulticon')){
							$i->icon=$params->get('defaulticon');
						}else
							$i->icon="./media/com_simplefilemanager/images/download.png";
					}
				?>
				<td>
					<?php if( $i->license): ?>
					<div class="sfm-element">
						<?php if( $i->license_link ) $i->license='<a href="'.$i->license_link.'" target="_blank">'.$i->license.'</a>' ;?>
						<?php echo $i->license; ?>
					</div>
					<?php endif; ?>
				
					<?php if ($i->md5hash): ?>
						<div class="sfm-element">
							MD5: <?php echo $i->md5hash; ?>
						</div>
					<?php endif; ?>
					<?php if ($app->input->get('showSize',1,"int")): ?><?php echo round($i->file_size * .0009765625, 2); ?> Kb<br><?php endif; ?>

				</td>
			</tr>
			
		<?php endforeach; ?>
		</tbody>
  </table>
</div>
