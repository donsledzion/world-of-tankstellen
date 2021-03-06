<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline">
            {{config('app.name')}}
        </h2>
    </x-slot>
    <style>
        div.scroll {
            background-color: #fed9ff;
            width: auto;
            height: 480px;
            overflow-x: hidden;
            overflow-y: auto;
            text-align: center;
            padding: 5px;
        }

        ::-webkit-scrollbar {
            width: 16px;
            height: 16px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            border-radius: 100vh;
            background: #edf2f7;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 100vh;
            border: 3px solid #edf2f7;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .customSwalBtn{
            background-image: url("{{asset('storage/img/icons/star.png')}}");
            background-size: 50px;
            width: 50px;
            height: 50px;
            /*background-color: rgba(214,130,47,1.00);*/
            border-left-color: rgba(214,130,47,1.00);
            border-right-color: rgba(214,130,47,1.00);
            border: 0;
            border-radius: 3px;
            box-shadow: none;
            color: black;
            cursor: pointer;
            font-size: 17px;
            font-weight: 500;
            margin: 2px;
            padding: 2px;
        }
        .customSwalBtn:hover{
            background-image: url("{{asset('storage/img/icons/star_hover.png')}}");
        }

        .sweetOpinion{
            border: 1px dotted black;
            border-radius: 10px;
            padding-top:8px;
            padding-bottom: 8px;
            margin: 4px;
        }
    </style>
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- component -->

                    <div id="map" data-size="big" class="map xl:inline-block" style="height: 90vh; width: 80%; display: inline-block;"></div>

                    <div id="side" data-size="big" class="bar align-top" style="height: 90vh; width: 19%; margin-left:5px; display: inline-block;">
                        <div class="px-5 py-3 shadow overflow-hidden border-b bg-blue-300 border-gray-200 sm:rounded-lg" style="margin-bottom: 10px;">
                            <div>
                                <label for="search_city" class="font-bold block text-sm" >Wpisz miasto aby wyszuka?? stacje paliw:</label>
                                <input id="search_city" class="search_city w-40" type="text" placeholder="podaj miasto">
                                <button class="search_button btn-info bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="button">SZUKAJ</button>
                            </div>
                            <div>
                                <label for="search_radius" class="font-bold block text-sm" >Podaj promie?? wyszukiwania od ??rodka mapy: [km]</label>
                                <input id="search_radius" class="search_radius w-40" type="number" step="1" placeholder="Podaj promie??" value="10">
                            <button class="search_button_radius btn-info bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="button">SZUKAJ</button>
                            </div>
                        </div>
                        {{--======================================================================================--}}


                        <div class="flex flex-col text-left">
                            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                    <div class="scroll shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                        <table id="stations-list" class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Nazwa
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Adres
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Oceny
                                                </th>
                                                <th scope="col" class="relative px-2 py-2 text-center text-xs">
                                                    Trasa
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200" id="stations_table">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button id="hide-button" class="p-2 pl-5 pr-5 bg-transparent border-2 border-yellow-500 text-yellow-500 text-lg rounded-lg hover:bg-yellow-500 hover:text-gray-100 focus:border-4 focus:border-yellow-300">Schowaj</button>
                        </div>
                        {{--======================================================================================--}}

                    </div>
                    <div id="found_atms"></div>


                    <!-- component -->
                </div>
            </div>
        </div>

    </div>
    <script src="https://www.openlayers.org/api/OpenLayers.js"></script>
    <script type="text/javascript">
        const avatarsUrl =  "{{ asset('storage/img/avatars/') }}/" ;
        const pinsUrl =  "{{ asset('storage/img/pins/') }}/" ;
        const starUrl = "{{asset('storage/img/icons/star.png')}}";
        const starMiniUrl = "{{asset('storage/img/icons/star_mini.png')}}";
        const baseUrl = "{{asset('')}}";
        const geoJson = "{{asset('storage/route.json')}}";
        const routeIcon = "{{asset('storage/img/icons/route.png')}}";
        var startPoint = [18.61245, 54.37174] ;
        var stationsFound = 0 ;
        var lastSearch = [18.61245, 54.37174];


        const formatter = new Intl.NumberFormat('pl-PL', {
            maximumFractionDigits: 1,
            minimumFractionDigits: 1
        });

        var stationRate = 1;

        const user_id = "{{Auth::id()}}";
        if(user_id) {
            console.log("User id: " + user_id);
        } else {
            console.log("user id is NULL");
        }

        function sweetException(e){
            Swal.fire(
                'Error',
                'Error: '+e.toString(),
                'error'
            )
        }

        function sweetFound(count){
            if(count>0) {
                Swal.fire(
                    'Mamy to!',
                    'Znaleziono ' + count + ' stacji',
                    'info'
                )
            } else {
                Swal.fire(
                    'Niestety!',
                    'Nie znaleziono stacji spe??niaj??cych kryteria wyszukiwania',
                    'info'
                )
            }
        }

        function sweetFail(jqXHR){
            Swal.fire(
                'Niepowodzenie',
                responseError(jqXHR.status),
                'error'
            )
        }

        function listStations(stations) {
            stationRate = 1;
            $("#stations-list tbody").empty();
            source.clear();
            $.each(stations.response.venues, function ($key, $value) {
                let name = /*$value.tags.operator ?? $value.tags.brand ??*/ $value.name ?? "";
                let address = ($value.location.city ?? "") + " - " + ($value.location.address ?? "") + " ";
                let lon = $value.location.lng;
                let lat = $value.location.lat;
                render_map([lon, lat]);
                addFeatureLonLat(lon,lat,name);
                $('#stations_table').append('' +
                    '<tr>' +
                        '<td class="px-2 py-2 w-8 whitespace-normal content-center">' +
                            '<div class="flex items-center">' +
                                '<div class="flex-shrink-0 h-10 w-10">' +
                                    '<img id="avatar_' + $key + '" class="h-10 w-10 rounded-full"' +
                                        'src=""' +
                                        'alt="">' +
                                '</div>' +
                                '<div class="ml-4">' +
                                    '<div class="text-xs font-medium text-center text-gray-900">' +
                                        '<button class="zoom_station" type="button" data-lon="' + lon + '" data-lat="' + lat + '"><b>'
                                            + name +
                                        '</b></button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</td>' +

                        '<td class="px-1 py-2 whitespace-normal content-center">' +
                            '<div class="text-xs w-20 text-gray-900 text-center ">'+ address +'</div>' +
                        '</td>' +

                        '<td class="px-1 py-1 whitespace-nowrap content-center">' +
                            '<button class="get_opinions content-center text-center text-indigo-600 hover:text-indigo-900"' +
                                ' data-station-id="' + $value.id + '"><img src=" ' + starMiniUrl + ' " style="height:20px;"></button>' +
                        '</td>' +

                        '<td class="px-1 py-2 block whitespace-nowrap text-center text-xs font-medium content-center">' +
                            '<button class="route_button block content-center text-center text-indigo-600 hover:text-indigo-900"' +
                                ' data-station-id="' + $value.id + '"  data-lon="' + lon + '" data-lat="' + lat + '">' +
                                '<img src="' + routeIcon + '" style="height: 20px;">' +
                            '</button>' +
                        '</td>' +
                    '</tr>');

                try {
                    $('#avatar_' + $key).attr('src', getAvatar(avatarsUrl,name));
                } catch(e){
                    $('#avatar_' + $key).attr('src', (avatarsUrl + 'tankstelle.png'));
                }
            });
        }

        function responseError(responseCode){
            switch (responseCode) {
                case 429:
                    return "Zbyt wiele zapyta??. Spr??buj ponownie za chwil??"
                case 500:
                    return "B????d serwera. Nie wpisuj g??upot."
                case 504:
                    return "Brak odpowiedzi. Spr??buj jeszcze raz."
                default:
                    return "Nieokre??lony b????d. (Ups...)"
            }
        }

        let view = new ol.View({
            center: ol.proj.fromLonLat(startPoint),
            zoom: 18
        });

        var source = new ol.source.OSM();

        let map = new ol.Map({
            target: 'map',
            layers: [
                new ol.layer.Tile({
                    source: source
                })
            ],
            view: view
        });

        source = new ol.source.Vector({
            features: [
                new ol.Feature({
                    geometry: new ol.geom.Point(ol.proj.fromLonLat(startPoint))
                })
            ]
        });

        source2 = new ol.source.Vector({

        });

        var layer2 = new ol.layer.Vector({
            source: source2
        });
        var layer = new ol.layer.Vector({
            source: source
        });
        map.addLayer(layer);
        map.addLayer(layer2);


        const styles = {
            route: new ol.style.Style({
                stroke: new ol.style.Stroke({
                    width: 6, color: [40, 40, 40, 0.8]
                })
            }),
            icon: new ol.style.Style({
                image: new ol.style.Icon({
                    anchor: [0.5, 1],
                    src: pinsUrl + 'dot.png'
                })
            })
        };

        map.on('click', function(evt){
            source2.clear();
            startPoint = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326') ;
            console.log(startPoint);
            if(stationsFound>0){
                findAndShowRoute(startPoint, lastSearch);
            }
        });

        function addFeatureLonLat(lon, lat,name){

            var marker = new ol.Feature({
                geometry: new ol.geom.Point(ol.proj.fromLonLat([lon, lat])),
            });

            marker.setStyle(new ol.style.Style({
                image: new ol.style.Icon(({
                    anchor: [0.5,1],
                    scale: 0.3,
                    crossOrigin: 'anonymous',
                    src: getAvatar(pinsUrl,name),
                })),
                // ======================================================================

                text: new ol.style.Text({
                    font: '12px Calibri,sans-serif',
                    fill: new ol.style.Fill({ color: '#000' }),
                    stroke: new ol.style.Stroke({
                        color: '#fff', width: 2
                    }),
                    // get the text from the feature - `this` is ol.Feature
                    // and show only under certain resolution
                    text: map.getView().getZoom() > 12 ? '' : ''
                })

                // ======================================================================
            }));

            source.addFeature(marker);
        }


        function addDotMarkerLonLat(lon, lat, name=null){

            var marker = new ol.Feature({
                geometry: new ol.geom.Point(ol.proj.fromLonLat([lon, lat])),
            });

            if(!name){
                name = "dot";
            }

            marker.setStyle(new ol.style.Style({
                image: new ol.style.Icon(({
                    anchor: [0.5,1],
                    scale: 0.3,
                    crossOrigin: 'anonymous',
                    src: getAvatar(pinsUrl,name),
                }))
            }));

            source2.addFeature(marker);
        }

        function findAndShowRoute(routeFrom, routeTo){

            var startString = routeFrom[0] + ',' + routeFrom[1];
            var endString = routeTo[0] + ',' + routeTo[1];
            /*source2.clear();*/
            addDotMarkerLonLat(routeFrom[0],routeFrom[1],'start');

            $.ajax({
                method: 'get',
                format: 'json',
                url:    'https://router.project-osrm.org/route/v1/driving/' + startString + ';' + endString + '?overview=full&geometries=polyline6'
            }).done(function(response){

                drawRoute(response.routes[0]);

            }).fail(function(response){
                console.log('Fail response: '+response);

            });
        }


        function drawRoute(geometry){

            console.log(geometry);

            var route = new ol.format.Polyline({
                factor: 1e6
            }).readGeometry(geometry.geometry, {
                dataProjection: 'EPSG:4326',
                featureProjection: 'EPSG:3857'
            });
            var feature = new ol.Feature(route);
            feature.setStyle(styles.route);
            /*source2.clear();*/

            source2.addFeature(feature);
            view.fit(route, {
                size: [450,450],
                duration: 1000,
            });
        }

        function render_map(location=startPoint, zoom_lvl=18) {
            view.setCenter(ol.proj.fromLonLat(location));
            view.setZoom(zoom_lvl);
        }

        function find_stations(city) {
            //===================================================================================
            stationsFound = 0 ;
            Swal.fire({
                title: 'Szukam stacji w mie??cie ' + city,
                html: 'to potrwa tylko chwilk??',
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.timer) {
                    console.log('I was closed by the timer')
                }
            })
            //======================================================================
            $.ajax({
                dataType: "json",
                url: "https://api.foursquare.com/v2/venues/search",
                data: {
                    client_id:      "{{config('services.places_api.client_id')}}",
                    client_secret:  "{{config('services.places_api.secret')}}",
                    v:              '20180323',
                    limit:          100,
                    categoryId:     '4bf58dd8d48988d113951735',
                    near: city,
                    radius: 2500,
                },
            }).done(function (pois) {
                try {
                    console.log(pois);
                    let stations = JSON.parse(JSON.stringify(pois));
                    sweetFound(pois.response.venues.length);
                    stationsFound = pois.response.venues.length ;
                    listStations(stations);
                } catch(e){
                    sweetException(e);
                }
            }).fail(function (jqXHR, textStatus, error) {
                sweetFail(jqXHR);
            }).always(function () {
                console.log("complete");
            });

        }

        function find_stations_radius(lon,lat, radius) {
            console.log("Szukam stacji w promieniu " + radius + " od lokalizacji ["+lon+" , " +lat+"]");
            stationsFound = 0 ;
            Swal.fire({
                title: 'Zaczekj chwilk??',
                html: "Szukam stacji w promieniu " + (0.001*radius) + " km od wskazanej lokalizacji.",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                }
            }).then((result) => {
                /* Read more about handling dismissals below */
                if (result.dismiss === Swal.DismissReason.timer) {
                    console.log('I was closed by the timer')
                }
            })
            $.ajax({
                dataType: "json",
                url: "https://api.foursquare.com/v2/venues/search",
                data: {
                    client_id:      "{{config('services.places_api.client_id')}}",
                    client_secret:  "{{config('services.places_api.secret')}}",
                    v:              '20180323',
                    limit:          100,
                    ll:             ''+lon+','+lat+'',
                    categoryId:     '4bf58dd8d48988d113951735',
                    radius: radius,
                },
            }).done(function (pois) {
                let stations = JSON.parse(JSON.stringify(pois));
                sweetFound(stations.response.venues.length);
                stationsFound = stations.response.venues.length ;
                listStations(stations);
            }).fail(function (jqXHR, textStatus, error) {
                sweetFail(jqXHR);
            }).always(function () {
                console.log("complete");
            });

        }

        function getAvatar(path,name=null){

            let nameExists = false;

            if(name === null){
                return path + 'tankstelle.png';
            }

            $.ajax({
                url: path + name.toLowerCase() + '.png',
                async: false,
                error: function () {
                    nameExists = false;
                },
                success: function () {
                    nameExists = true;
                }
            });

            if(nameExists){
                return path + name.toLowerCase() + '.png';
            } else {
                return path + 'tankstelle.png';
            }
        }

        function saveText(text, filename){
            var a = document.createElement('a');
            a.setAttribute('href', 'data:text/plain;charset=utf-8,'+encodeURIComponent(text));
            a.setAttribute('download', filename);
            a.click()
        }

        function getOpinions(station_id){
                    $.ajax({
                        url: baseUrl + 'stations/' + station_id + '/opinions'
                    }).done(function(response){
                        let commentsList = ''
                        $.each(response.opinions, function ($key, $value) {
                            let createdAt = new Date($value.created_at);
                            let stars = '';
                            for(let i = 1 ; i <= $value.rate ; i++){
                                stars += '<img src="' + starMiniUrl + '" style="width:15px; display: inline-block">'
                            }
                            let comment = null;
                            if($value.comment) {
                                comment = $value.comment ;
                            } else {
                                comment = "<< brak komentarza >>"
                            }
                            commentsList += '<div class="sweetOpinion">' +
                                                '<div><p><i>"' + comment +'"</i></p>' +
                                                '<span>' + stars +' </span>' +
                                                '<span><b>' + $value.user.name + ' </b></span>' +
                                                '<span>' + createdAt.toLocaleDateString() + '</span></div>' +
                                            '</div>'
                        });
                        var addOpinionButton = '<button onclick="addOpinion('+station_id+')" class="bg-green-500 hover:bg-green-700 text-white text-center py-2 px-4 rounded">'+
                            'Dodaj swoj?? opini??' +
                            '</button>'
                        if(response.averageRate != null){
                            commentsList += '<div>??rednia ocena: <b>' + formatter.format(response.averageRate) + ' / 5</b></div>';
                        }
                        if(user_id){
                            commentsList += addOpinionButton;
                        }
                        if(response.averageRate != null) {
                            Swal.fire({
                                title: 'Opinie o stacji:',
                                html: commentsList,
                                icon: 'success'
                            })
                        } else {
                            Swal.fire({
                                title: 'Pusto',
                                html: '<div>nie ma jeszcze ??adnej opinii</div><div>' + addOpinionButton + '</div>',
                                icon: 'warning'
                            })
                        }
                    }).fail(function(response) {
                        console.log("co?? si?? wyjeba??o");
                        Swal.fire({
                            icon: 'error',
                            title: 'Ojojoj!',
                            html: response.message
                        })
                    });
        }

        $(function(){
            $('.search_button').click(function(){
                let typed_city = $('.search_city').val();
                find_stations(typed_city);
            });
            $('.search_button_atm').click(function(){
                getATMs();
            });
            $('.search_button_radius').click(function(){
                let center = ol.proj.toLonLat(map.getView().getCenter());
                let radius = $('#search_radius').val()*1000;
                find_stations_radius(center[1],center[0],radius);
            });

            $('#stations_table')
                .on('click','.zoom_station',function(){
                let lon = $(this).data("lon");
                let lat = $(this).data("lat");
                render_map([lon,lat]);
            })
                .on('click','.add_opinion',function(){
                addOpinion($(this).data("station-id"))
            })
                .on('click','.get_opinions',function(){
                getOpinions($(this).data("station-id"));
            }).on('click', '.route_button',function(){
                lastSearch = [$(this).data("lon"),$(this).data("lat")] ;
                source2.clear();
                findAndShowRoute(startPoint,lastSearch);
            });

            $('.customSwalBtn').on('click','swal2-shown',function(){
                console.log('clicked!');
            });

            $('#hide-button').click(function(){
                let state = $("#map").data("size");
                console.log("Size: " + state);
                if(state === 'big'){
                    $("#side").css('display','none');
                    $("#map").css('width','100%').append('<button id="show-button" class="p-2 pl-5 pr-5 bg-yellow-500 border-2 border-yellow-500 text-yellow-700 text-lg rounded-lg hover:bg-yellow-700 hover:text-gray-100 focus:border-4 focus:border-yellow-300" style="position:fixed; top: 140px; right: 80px; ">Poka?? opcje</button>');
                    map.updateSize();
                }
            });
            $('#map').on('click','#show-button',function(){
                $("#side").css('display','inline-block');
                $("#map").css('width','80%');
                map.updateSize();
                console.log('clicked!');
                $(this).remove();
            });

        });

        function switchMapAndTableSize(){

        }


        function assignRate(rate){
            console.log("rate assigned: "+rate);
            stationRate = rate;
        }

        function addOpinion(station_id){
            if(user_id) {
                Swal.fire({

                    title: 'Jak oceniasz t?? stacj???',
                    html: '<button type="button" role="button" onclick="assignRate(1)" tabindex="0" class="SwalBtn1 customSwalBtn"><b>1</b></button>' +
                        '<button type="button" role="button" onclick="assignRate(2)" tabindex="0" class="SwalBtn2 customSwalBtn"><b>2</b></button>' +
                        '<button type="button" role="button" onclick="assignRate(3)" tabindex="0" class="SwalBtn3 customSwalBtn"><b>3</b></button>' +
                        '<button type="button" role="button" onclick="assignRate(4)" tabindex="0" class="SwalBtn4 customSwalBtn"><b>4</b></button>' +
                        '<button type="button" role="button" onclick="assignRate(5)" tabindex="0" class="SwalBtn5 customSwalBtn"><b>5</b></button><br/><br/>' +
                        'Podziel si?? swoj?? opini??',

                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    cancelButtonText: 'Anuluj',
                    confirmButtonText: 'Dodaj',
                    showLoaderOnConfirm: true,
                    preConfirm: (comment) => {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        $.ajax({
                            method: 'post',
                            url: baseUrl + 'opinion',
                            dataType: 'json',
                            data: {
                                user_id: user_id,
                                station_id: station_id,
                                rate: stationRate,
                                comment: comment,
                            }
                        }).done(function (response) {
                            Swal.fire({
                                icon: `${response.status}`,
                                title: 'Gratulacje!',
                                html: response.message
                            })
                        }).fail(function (response) {
                            Swal.fire({
                                icon: `${response.status}`,
                                title: 'Ojojoj!',
                                html: response.message
                            })
                        })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Zaloguj si??!',
                    html: 'Musisz si?? zalogowa?? aby dodawa?? opinie'
                })
            }
        }


        function findATM(){
            $.ajax({
                method: 'post',
                url: baseUrl + 'opinion',
                dataType: 'json',
                data: {
                    user_id: user_id,
                    station_id: station_id,
                    rate: stationRate,
                    comment: comment,
                }
            }).done(function (response) {
                Swal.fire({
                    icon: `${response.status}`,
                    title: 'Gratulacje!',
                    html: response.message
                })
            }).fail(function (response) {
                Swal.fire({
                    icon: `${response.status}`,
                    title: 'Ojojoj!',
                    html: response.message
                })
            })
        }

        function getATMs(){
            $.ajax({
                dataType: "json",
                url: "https://api.foursquare.com/v2/venues/search?client_id={{config('services.places_api.client_id')}}&client_secret={{config('services.places_api.secret')}}&v=20180323&limit=100&ll=54.371570473891865,18.61244659572624&query=atm",
                data: {},
                success: function( response ) {
                    $.each(response.response.venues, function($key, $value){
                        $("#found_atms").append(""+"<div>"+($key+1)+" - "+$value.name+"");
                        console.log($value.name);
                    });
                    console.log(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Code for handling errors
                    console.log("wyjebka :(");
                    console.log(jqXHR);
                }
            });
        }


    </script>
</x-app-layout>
