<div id="Filter-listFilter" class="control-filter">
    <form>
            <div class="form-group text-center">
            <div data-control="datepicker" style="width: 30%;display: inline-block;">
                <label for=""><?= e(trans('jaber.player::lang.plugin.from_date')) ?></label>
                <input
                    type="date"
                    class="form-control"
                    placeholder="<?= e(trans('jaber.player::lang.plugin.select')) ?>"
                    name="from_date" />

            </div>
            <div data-control="datepicker" style="width: 30%;display: inline-block;">
                <label for=""><?= e(trans('jaber.player::lang.plugin.to_date')) ?></label>
                <input
                    type="date"
                    class="form-control"
                    placeholder="<?= e(trans('jaber.player::lang.plugin.select')) ?>"
                    name="to_date" />
            </div>
        </div>
        <!-- Submit Button -->
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary wn-icon-download" data-request="onReports" data-stripe-load-indicator><?= e(trans('jaber.player::lang.plugin.reports')) ?></button>
        </div>
    </form>
</div>

<style>
    #Filter-listFilter .form-group {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: row;
        gap: 20px;
    }

    #Filter-listFilter .form-group>div {
        flex: 1;
        text-align: center;
    }
</style>