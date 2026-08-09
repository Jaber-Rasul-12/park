<?= $this->makePartial('list_toolbar')  ?>

<button onclick='window.print()' data-hotkey='ctrl+p' id='print_button' style='margin-right:10px;' class='btn btn-default wn-icon-print'><?= e(trans("jaber.player::lang.plugin.print")) ?></button>
<div class="form-preview">
</div>
<p>
    <a href="<?= Backend::url('jaber/player/reports') ?>" class="btn btn-default wn-icon-chevron-left">
        <?= e(trans('backend::lang.form.return_to_list')) ?>
    </a>
</p>

<div class="stripe-loading-indicator loaded" id="idLoaded">
    <div class="stripe"></div>
    <div class="stripe-loaded"></div>
</div>