<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-tag"></i> <?= lang('menu_labels'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions'); ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu">
                        <li>
                            <a href="<?= site_url('system_settings/add_pos_type_label'); ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_label'); ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <p class="introtext"><?= lang('menu_labels'); ?> for POS type: <strong><?= htmlspecialchars($pos_type); ?></strong>. These labels are used in Admin and User menu.</p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="PosTypeLabelsTable" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th><?= lang('label_key'); ?></th>
                                <th><?= lang('label_value'); ?></th>
                                <th style="width:120px;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($labels)) { ?>
                                <?php foreach ($labels as $row) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row->label_key); ?></td>
                                        <td><?= htmlspecialchars($row->label_value); ?></td>
                                        <td>
                                            <div class="text-center">
                                                <a href="<?= site_url('system_settings/edit_pos_type_label/' . $row->id); ?>" data-toggle="modal" data-target="#myModal" class="tip" title="<?= lang('edit'); ?>">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="#" class="tip po" title="<b><?= lang('delete'); ?></b>" data-content="<p><?= lang('r_u_sure'); ?></p><a class='btn btn-danger po-delete-label' href='<?= site_url('system_settings/delete_pos_type_label/' . $row->id); ?>'><?= lang('i_m_sure'); ?></a> <button class='btn po-close'><?= lang('no'); ?></button>" rel="popover" data-html="true">
                                                    <i class="fa fa-trash-o"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="3" class="dataTables_empty"><?= lang('no_data_available'); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Ensure delete popover content is rendered as HTML (not raw text)
    $('.po').each(function() {
        try { $(this).popover('destroy'); } catch (e) {}
    });
    $('.po').popover({ html: true, placement: 'left', trigger: 'click', container: 'body' });
    $(document).on('click', '.po-close', function() { $('.po').popover('hide'); return false; });
    $(document).on('click', '.po-delete-label', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (href) { window.location.href = href; }
        return false;
    });
});
</script>
