<div id="vtr_{voteid}">
    <div class="form-group">
        <div class="col-sm-4">
            {l_voting:hdr.title}
        </div>
        <div class="col-sm-8">
            <input type="text" name="vname_{voteid}" value="{name}" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-4">
            {l_voting:hdr.descr}
        </div>
        <div class="col-sm-8">
            <textarea name="vdescr_{voteid}" rows="4" class="form-control">{descr}</textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            {l_voting:hdr.options}
        </div>
        <div class="col-sm-8">
            <div class="well">
                <div class="form-group">
                    <label><input type="checkbox" name="vactive_{voteid}" value="1" {vactive} /> {l_voting:flag.active}</label><br>
                    <label><input type="checkbox" name="vclosed_{voteid}" value="1" {vclosed} /> {l_voting:flag.closed}</label><br>
                    <label><input type="checkbox" name="vregonly_{voteid}" value="1" {vregonly} /> {l_voting:flag.regonly}</label><br>
                </div>
                <input type="button" value="{l_voting:button.delete}" onclick="return confirmIt('{php_self}?mod=extra-config&plugin=voting&action=delvote&id={voteid}', '{l_sure_del}');" class="btn btn-danger" />
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-4">
            {l_voting:choise.title}
        </div>
        <div class="col-sm-8">
            <table class="table table-bordered table-condensed" id="vlist_{voteid}">
                <thead>
                    <tr>
                        <th>{l_voting:choise.title}</th>
                        <th colspan="2">{l_voting:choise.number} ({allcnt})</th>
                        <th>{l_voting:choise.active}</th>
                        <th>{l_voting:choise.delete}</th>
                    </tr>
                </thead>
                <tbody>
                    {entries}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5">
                            <input type="button" value="{l_voting:choise.button.add}" onclick="createVLine({voteid});" class="btn btn-primary" /></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
