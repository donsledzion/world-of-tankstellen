<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline">
            Mapy
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- component -->

                    <div id="map" class="map" style="height: 600px; width: 60%; display: inline-block;"></div>
                    <div id="side" class="bar align-top" style="height: 600px; width: 39%; margin-left:5px; display: inline-block;">
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
                        {{--======================================================================================--}}

                        <style>
                            div.scroll {
                                background-color: #fed9ff;
                                width: auto;
                                height: 470px;
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
                                                    Akcje
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


                        {{--======================================================================================--}}
                    </div>


                    <!-- component -->
                </div>
            </div>
        </div>

    </div>
    <script src="http://www.openlayers.org/api/OpenLayers.js"></script>
    <script type="text/javascript">
        const avatarsUrl =  "{{ asset('storage/img/avatars/') }}/" ;
        const pinsUrl =  "{{ asset('storage/img/pins/') }}/" ;
        const starUrl = "{{asset('storage/img/icons/star.png')}}";
        const starMiniUrl = "{{asset('storage/img/icons/star_mini.png')}}";
        const baseUrl = "{{asset('')}}"


        var stationRate = 1;

        const user_id = "{{Auth::id()}}";
        console.log("User id: "+user_id);

        function sweetException(e){
            Swal.fire(
                'Error',
                'Error: '+e.toString(),
                'error'
            )
        }

        function sweetFound(count){
            Swal.fire(
                'Mamy to!',
                'Znaleziono ' + count + ' stacji',
                'info'
            )
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
            console.log("============================================");
            console.log("================== STATIONS ================");
            console.log("============================================");
            $("#stations-list tbody").empty();
            $.each(stations.elements, function ($key, $value) {
                let name = $value.tags.operator ?? $value.tags.brand ?? $value.tags.name;
                let address = ($value.tags['addr:city'] ?? "") + " - " + ($value.tags['addr:street'] ?? "") + " " + ($value.tags['addr:housenumber'] ?? "");
                let lon = $value.lon;
                let lat = $value.lat;
                console.log(name + ", " + address);
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
                                        '<button class="zoom_station" type="button" data-lon="' + lon + '" data-lat="' + lat + '">'
                                            + name +
                                        '</button>' +
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

                        '<td class="px-1 py-2 whitespace-nowrap text-center text-xs font-medium content-center">' +
                            '<button class="add_opinion content-center text-center text-indigo-600 hover:text-indigo-900"' +
                            ' data-station-id="' + $value.id + '">oceń</button>' +
                        '</td>' +
                    '</tr>');

                try {
                    $('#avatar_' + $key).attr('src', getAvatar(avatarsUrl,name));
                } catch(e){
                    console.log('nie znaleziono zdjęcia');
                    $('#avatar_' + $key).attr('src', (avatarsUrl + 'tankstelle.png'));
                }
            });
        }

        function responseError(responseCode){
            switch (responseCode) {
                case 429:
                    return "Zbyt wiele zapytań. Spróbuj ponownie za chwilę"
                case 500:
                    return "Błąd serwera. Nie wpisuj głupot."
                case 504:
                    return "Brak odpowiedzi. Spróbuj jeszcze raz."
                default:
                    return "Nieokreślony błąd. (Ups...)"
            }
        }

        let view = new ol.View({
            center: ol.proj.fromLonLat([18.61245, 54.37174]),
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
                    geometry: new ol.geom.Point(ol.proj.fromLonLat([18.61245, 54.37174]))
                })
            ]
        });

        var layer = new ol.layer.Vector({
            source: source
        });
        map.addLayer(layer);


        function addFeatureLonLat(lon, lat,name){
            var marker = new ol.Feature({
                geometry: new ol.geom.Point(ol.proj.fromLonLat([lon, lat])),
            });

            marker.setStyle(new ol.style.Style({
                image: new ol.style.Icon(({
                    anchor: [0.5,1],
                    scale: 0.3,
                    /*size: [140,260],*/
                    crossOrigin: 'anonymous',
                    src: getAvatar(pinsUrl,name),

                }))
            }));

            source.addFeature(marker);
        }




        function render_map(location=[18.61245, 54.37174], zoom_lvl=18) {
            view.setCenter(ol.proj.fromLonLat(location));
            view.setZoom(zoom_lvl);
        }

        function find_stations(city) {
            //===================================================================================
            let timerInterval
            Swal.fire({
                title: 'Szukam stacji w mieście ' + city,
                html: 'to potrwa tylko chwilkę',
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
                try {
                    let stations = JSON.parse(JSON.stringify(pois));
                    sweetFound(stations.elements.length);
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
            Swal.fire({
                title: 'Zaczekj chwilkę',
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
                sweetFound(stations.elements.length);
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
                    console.log('file does not exists');
                    nameExists = false;
                },
                success: function () {
                    console.log('file exists');
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
                        if(commentsList !== '') {
                            Swal.fire({
                                title: 'Opinie o stacji:',
                                html: commentsList,
                                icon: 'success'
                            })
                        } else {
                            Swal.fire({
                                title: 'Pusto',
                                html: 'nie ma jeszcze żadnej opinii',
                                icon: 'warning'
                            })
                        }
                    }).fail(function(response) {
                        console.log("coś się wyjebało");
                        Swal.fire({
                            icon: `${response.status}`,
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
                console.log("Station id: "+$(this).data("station-id"));
                addOpinion($(this).data("station-id"))
            })
                .on('click','.get_opinions',function(){
                console.log("Station id: "+$(this).data("station-id"));
                getOpinions($(this).data("station-id"));
            });

            $('.add_opinion').click(function(){
                addOpinion('terefere');
            });

            $('.customSwalBtn').on('click','swal2-shown',function(){
                console.log('clicked!');
            });

        });

        function assignRate(rate){
            console.log("rate assigned: "+rate);
            stationRate = rate;
        }

        function addOpinion(station_id){

            Swal.fire({

                title: 'Jak oceniasz tę stację?',
                html:   '<button type="button" role="button" onclick="assignRate(1)" tabindex="0" class="SwalBtn1 customSwalBtn"><b>1</b></button>' +
                        '<button type="button" role="button" onclick="assignRate(2)" tabindex="0" class="SwalBtn2 customSwalBtn"><b>2</b></button>' +
                        '<button type="button" role="button" onclick="assignRate(3)" tabindex="0" class="SwalBtn3 customSwalBtn"><b>3</b></button>' +
                        '<button type="button" role="button" onclick="assignRate(4)" tabindex="0" class="SwalBtn4 customSwalBtn"><b>4</b></button>' +
                        '<button type="button" role="button" onclick="assignRate(5)" tabindex="0" class="SwalBtn5 customSwalBtn"><b>5</b></button><br/><br/>' +
                        'Podziel się swoją opinią',

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
                        data:{
                            user_id: user_id,
                            station_id: station_id,
                            rate: stationRate,
                            comment: comment,
                        }
                    }).done(function(response){
                        Swal.fire({
                            icon: `${response.status}`,
                            title: 'Gratulacje!',
                            html: response.message
                        })
                    }).fail(function(response){
                        Swal.fire({
                            icon: `${response.status}`,
                            title: 'Ojojoj!',
                            html: response.message
                        })
                    })
                },
                allowOutsideClick: () => !Swal.isLoading()
            })
        }

    </script>
</x-app-layout>
