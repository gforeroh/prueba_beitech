$(document).ready(function () {
    moment.locale('es');
    var rootService = 'http://localhost/prueba_beitech';

    let customerId = null;
    let timeStampStart = null;
    let timeStampEnd = null;
    var flag = false;

    // timeStampStart = moment("2019-01-02", "YYYY-MM-DD").unix();
    // timeStampEnd = moment("2019-01-02", "YYYY-MM-DD").unix();

    getOrder = async (customerId, timeStampStart, timeStampEnd) => {
        const settings = {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        };
        
        let url = `${rootService}/Orders/orders?customer_id=${customerId}&timeStampStart=${timeStampStart}&timeStampEnd=${timeStampEnd}`;        
        const data = await fetch(url, settings)
            .then(response => response.json())
            .then(json => {
                let { customersList, orders } = json;
                fnFillTable(orders);
                
                if (!flag){
                    let options = `<option value="0">-- Seleccione un cliente --</option>`;
                    $.each(customersList, function (key, value) {
                        options += `<option value="${key}">${value}</option>`;
                    });
                    
                    $('#customer_id')
                        .empty()
                        .append(options);
                    
                    flag = true;
                }
                
                return json;
            })
            .catch(e => {
                return e
            });

        return data;
    }

    getOrder(customerId, timeStampStart, timeStampEnd);

    $('#btn-consultar').on('click', function(){
        let customerId = $('#customer_id').val();
        let reportrange = $('#reportrange').val();

        if (customerId == '0' || customerId == ''){
            alert("Debe seleccionar un cliente");
            return false;
        }

        let rangeDate = reportrange.split(' - ');
        let timeStampStart = moment(rangeDate[0], "DD/MM/YYYY").unix();
        let timeStampEnd = moment(rangeDate[1], "DD/MM/YYYY").unix();

        getOrder(customerId, timeStampStart, timeStampEnd);

    });
    
});

function ajax(object) {
    return new Promise(function (resolve, reject) {
        $.ajax(object).done(resolve).fail(reject);
    });
}

function fnFillTable(data){    
    if ($.fn.dataTable.isDataTable('#table-order')) {
        // table.draw();
    }
    
    var table = $('#table-order').DataTable({
        language: {
            "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json"
        },
        sPaginationType: "full_numbers",
        data: data,
        columnDefs: [
            {
                targets: [0],
                title: "Fecha Creación",
                data: "creation_date",
                visible: true,
                orderable: false,
                className: 'center',
                render: function (data, type, row) {
                    data = (data != null) ? moment(data, 'YYYY-MM-DDTHH:ss').format('DD-MM-YYYY') : '';
                    return data;
                },
            },
            {
                targets: [1],
                title: "Orden ID",
                data: "order_id",
                visible: true,
                orderable: true,
                className: '',
                render: function (data, type, row) {
                    return data;
                },
            },
            {
                targets: [2],
                title: "Total $",
                data: "total",
                visible: true,
                orderable: false,
                className: '',
                render: function (data, type, row) {
                    data = addMiles(data);
                    return "$ " + data;
                },
            },
            {
                targets: [3],
                title: "Direccion de envio",
                data: "delivery_address",
                visible: true,
                orderable: false,
                className: 'center',
                render: function (data, type, row) {
                    return data;
                },
            },
            {
                targets: [4],
                title: "Productos",
                data: "order_id",
                visible: true,
                orderable: false,
                className: 'center',
                render: function (data, type, row) {
                    let { order_detail } = row;
                    let str = ``;

                    $.each(order_detail, function (key, value) {
                        str += `${value.quantity} x ${value.product.name}`;
                        str += (key < order_detail.length) ? `<br/>\n` : ``;
                    });
                    
                    return str;
                },
            }
        ],
        "bDestroy": true,
        initComplete: function () {
        }
    });
}

function addMiles(nStr) {
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + '.' + '$2');
    }
    return x1 + x2;
}


$(function () {

    var start = moment().subtract(30, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Hoy': [moment(), moment()],
            'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Los últimos 7 días': [moment().subtract(6, 'days'), moment()],
            'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
            'Este mes': [moment().startOf('month'), moment().endOf('month')],
            'El mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            customRangeLabel: 'Rango fechas',
        }
    }, cb);

    cb(start, end);

});