

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td><span class="cmdAttr" data-l1key="id"></span></td>';
    tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="name"></td>';
    tr += '<td><span class="type" type="' + init(_cmd.type) + '">' + jeeFrontEnd.language.getMessage('type' + init(_cmd.type).charAt(0).toUpperCase() + init(_cmd.type).substr(1)) + '</span><span class="subType" subType="' + init(_cmd.subType) + '"></span></td>';
    tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité"></td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
}
