<?php if (isset($component)) { $__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\AppLayout::class, []); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline">
            Mapy
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- component -->

                    <div id="map" class="map" style="height: 600px; width: 60%; display: inline-block;"></div>
                    <div id="side" class="bar align-top" style="height: 600px; width: 35%; display: inline-block;">
                        <div>
                            <label for="search_city" >Wpisz miasto aby wyszukać stacje paliw:</label>
                            <input id="search_city" class="search_city" type="text" placeholder="podaj miasto">
                            <button class="search_button btn-info bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="button">SZUKAJ</button>
                        </div>
                        <div>
                            <label for="search_radius" >Podaj promień wyszukiwania od środka mapy: [km]</label>
                            <input id="search_radius" class="search_radius" type="number" step="1" placeholder="Podaj promień" value="10">
                        <button class="search_button_radius btn-info bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="button">SZUKAJ</button>
                        </div>
                        <table>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>id</th>
                                <th>name</th>
                                <th>adress</th>
                            </tr>
                            </thead>

                            <tbody id="stations_table">

                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            </tbody>

                        </table>

                    </div>


                    <!-- component -->
                </div>
            </div>
        </div>

    </div>
    <script type="text/javascript">


        let view = new ol.View({
            center: ol.proj.fromLonLat([18.61245, 54.37174]),
            zoom: 18
        });

        let map = new ol.Map({
            target: 'map',
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM()
                })
            ],
            view: view
        });
        function render_map(location=[18.61245, 54.37174], zoom_lvl=18) {
            view.setCenter(ol.proj.fromLonLat(location));
            view.setZoom(zoom_lvl);
        }
function find_stations(city) {
    console.log("Szukam stacji w mieście:" + city);
    $.ajax({
        url:
            'https://www.overpass-api.de/api/interpreter?' +
            'data=[out:json][timeout:60];' +
            'area["boundary"~"administrative"]["name"~"' + city + '"];' +
            'node(area)["amenity"~"fuel"];' +
            'out;',
        dataType: 'json',
        type: 'GET',
        async: true,
        crossDomain: true
    }).done(function (pois) {
        let stations = JSON.parse(JSON.stringify(pois));
        console.log("raw response:");
        console.log(pois);
        console.log("============================================");
        console.log("parsed:");
        console.log(stations.elements);
        //saveText( JSON.stringify(pois), "filename.json" );
        console.log("============================================");
        console.log("================== STATIONS ================");
        console.log("============================================");
        $.each(stations.elements, function ($key, $value) {
            let name = $value.tags.operator ?? $value.tags.brand ?? $value.tags.name ;
            let adress = ($value.tags['addr:city'] ?? "") + "," +($value.tags['addr:street'] ?? "") + " " + ($value.tags['addr:housenumber'] ?? "") ;
            let lon = $value.lon;
            let lat = $value.lat;
            console.log(name + ", " + adress);
            render_map([lon,lat]);
            $('#stations_table').append('<tr>' +
                    '<td><input type="checkbox"></td>' +
                    '<td>'+$value.id+'</td>' +
                    '<td ><button class="zoom_station" type="button" data-lon="'+lon+'" data-lat="'+lat+'">'
                        +name+
                    '</button></td>' +
                    '<td>'+adress +'</td>' +
                '</tr>');
        });
        console.log("============================================");
    }).fail(function (error) {
        console.log(error);
        console.log("error");
    }).always(function () {
        console.log("complete");
    });

}

        function find_stations_radius(lon,lat, radius) {
            console.log("Szukam stacji w promieniu " + radius + " od lokalizacji ["+lon+" , " +lat+"]");
            $.ajax({
                url:
                    'https://www.overpass-api.de/api/interpreter?' +
                    'data=[out:json][timeout:60];' +
                    'area' +
                        '(around:'+radius+','+lon+','+lat+');' +
                    'node(around:'+radius+','+lon+','+lat+')["amenity"~"fuel"];' +
                    'out;',
                dataType: 'json',
                type: 'GET',
                async: true,
                crossDomain: true
            }).done(function (pois) {
                let stations = JSON.parse(JSON.stringify(pois));
                console.log("raw response:");
                console.log(pois);
                console.log("============================================");
                console.log("parsed:");
                console.log(stations.elements);
                //saveText( JSON.stringify(pois), "filename.json" );
                console.log("============================================");
                console.log("================== STATIONS ================");
                console.log("============================================");
                $.each(stations.elements, function ($key, $value) {
                    let name = $value.tags.operator ?? $value.tags.brand ?? $value.tags.name ;
                    let adress = ($value.tags['addr:city'] ?? "") + "," +($value.tags['addr:street'] ?? "") + " " + ($value.tags['addr:housenumber'] ?? "") ;
                    let lon = $value.lon;
                    let lat = $value.lat;
                    console.log(name + ", " + adress);
                    render_map([lon,lat]);
                    $('#stations_table').append('<tr>' +
                        '<td><input type="checkbox"></td>' +
                        '<td>'+$value.id+'</td>' +
                        '<td ><button class="zoom_station" type="button" data-lon="'+lon+'" data-lat="'+lat+'">'
                        +name+
                        '</button></td>' +
                        '<td>'+adress +'</td>' +
                        '</tr>');
                });
                console.log("============================================");
            }).fail(function (error) {
                console.log(error);
                console.log("error");
            }).always(function () {
                console.log("complete");
            });

        }

function saveText(text, filename){
    var a = document.createElement('a');
    a.setAttribute('href', 'data:text/plain;charset=utf-8,'+encodeURIComponent(text));
    a.setAttribute('download', filename);
    a.click()
}

$(function(){
    //render_map();
    $('.search_button').click(function(){
        let typed_city = $('.search_city').val();
        find_stations(typed_city);
        render_map([17.706081,54.756605]);
    });
    $('.search_button_radius').click(function(){
        let center = map.getView().getCenter()
        console.log("Map center: "+center);
        //let typed_city = $('.search_city').val();
        //find_stations(typed_city);
        find_stations_radius(54.756605,17.706081,15000);
    });

    $('#stations_table').on('click','.zoom_station',function(){
        console.log('clicked');
        let lon = $(this).data("lon");
        let lat = $(this).data("lat");
        render_map([lon,lat]);
    });

});

    </script>
 <?php if (isset($__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da)): ?>
<?php $component = $__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da; ?>
<?php unset($__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\world-of-tankstellen\resources\views\maps\show.blade.php ENDPATH**/ ?>