function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }

    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    
    // ID
    tr += '<td><span class="cmdAttr" data-l1key="id"></span></td>';
    
    // Nom
    tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="name"></td>';
    
    // Type et Sous-type
    tr += '<td>';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + init(_cmd.type) + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"> (' + init(_cmd.subType) + ')</span>';
    tr += '</td>';
    
    // Unité
    tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité"></td>';
    
    // Actions (Boutons Tester et Configuration)
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>';
    }
    tr += '</td>';
    
    tr += '</tr>';
    
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
}
