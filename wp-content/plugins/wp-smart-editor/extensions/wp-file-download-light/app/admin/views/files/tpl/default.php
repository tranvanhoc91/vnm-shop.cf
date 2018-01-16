<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

// No direct access.
defined( 'ABSPATH' ) || die();

$items_thead = array(
    'ext' => __('#','wp-smart-editor'),
    'title' => __('Title','wp-smart-editor'),
    'size' => __('File size','wp-smart-editor'),
    'created_time' => __('Date added','wp-smart-editor'),
    'modified_time' => __('Date modified','wp-smart-editor'),
    'version' => __('Version','wp-smart-editor'),
    'hits' => __('Hits','wp-smart-editor'),
    'actions' => __('Actions','wp-smart-editor')
);
?>
<table class="restable">
    <thead>
    <tr>
        <?php
        foreach ($items_thead as $thead_key => $thead_text) {
            $icon = '';
            if ($thead_key === $this->ordering) {
                $icon = '<span class="dashicons dashicons-arrow-' . ($this->orderingdir === 'asc' ? 'up' : 'down') . '"></span>';
            }
            ?>
            <th class="<?php echo $thead_key; ?>">
                <?php if ($thead_key == 'actions') { ?>
                    <?php echo $thead_text; ?>
                <?php } else { ?>
                    <a href="#" class="<?php echo($this->ordering === $thead_key ? 'currentOrderingCol' : ''); ?>"
                       data-ordering="<?php echo $thead_key; ?>"
                       data-direction="<?php echo $this->orderingdir; ?>"><?php echo $thead_text; ?><?php echo $icon; ?></a>
                <?php } ?>
            </th>
        <?php } ?>
    </tr>
    </thead>
    <tbody >
    <?php if(is_array($this->files) || is_object($this->files)):?>
    <?php foreach ($this->files as $file): ?>
        <tr class="file" data-id-file="<?php echo $file->ID; ?>">
            <td class="ext <?php echo $file->ext; ?>"><span class="txt"><?php echo $file->ext; ?></span></td>
            <td class="title"><?php echo $file->post_title; ?></td>
            <td class="size"><?php echo ($file->size == 'n/a') ? $file->size : wpfdHelperFiles::bytesToSize($file->size); ?></td>
            <td class="created"><?php echo mysql2date(wpfdBase::loadValue($this->params, 'date_format', get_option('date_format')), $file->created); ?></td>
            <td class="modified"><?php echo mysql2date(wpfdBase::loadValue($this->params, 'date_format', get_option('date_format')), $file->modified); ?></td>
            <td class="version"><?php echo $file->version; ?></td>
            <td class="hits"><?php echo $file->hits.' '.__('hits','wp-smart-editor'); ?></td>
            <td class="actions"><a class="trash"><i class="icon-trash"></i></a> <a href="<?php echo $file->linkdownload;?>" class="download"><i class="icon-download"></i></a></td>
        </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>

</table>