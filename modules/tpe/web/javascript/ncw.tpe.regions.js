ncw.tpe.regions = {};

/**
 * letter
 */
ncw.tpe.regions = function () {
    ncw.onLoad(ncw.tpe.regions.countries);
};

/**
 * countries
 */
ncw.tpe.regions.countries = function () {   
    ncw.tpe.regions.countries.add();
};

/**
 * add country
 */
ncw.tpe.regions.countries.add = function () {
    $('#ncw-add-country').click(
        function () {     
            var country_id = $('#region_country').val();
            $.get(
                ncw.url(
                    '/tpe/region/addcountry/' + 
                    $('#region_id').val() + '/' + 
                    country_id
                ), 
                null,
                function (data) {
                    var name = data.country.name;
                    $('#ncw-countries').append(
                        '<tr id="ncw-added-country-' + country_id + '">' + 
                        '<td><a href="' + ncw.url('/tpe/countrysite/edit/' + country_id) + '">' + name  + '</a></td>' + 
                        '<td class="ncw-table-td-icons">' + 
                        '<a href="http://localhost/Development/php/netzcraftwerk3/admin/tpe/countrysite/edit/' + country_id + '/' + $('#region_id').val() + '"><img src="' + ncw.image('icons/16px/pencil.png') + '" alt="edit" title="edit" /></a> ' + 
                        '<a href="javascript: ncw.tpe.regions.countries.remove(' + $('#region_id').val() + ', ' + country_id + ');"><img title="delete" alt="delete" src="' + ncw.image('icons/16px/delete.png') + '"/></a>' + 
                        '</td>' + 
                        '</tr>'
                    );
                    ncw.layout.table();
                },
                'json'
            );            
        }
    );        
};

/**
 * remove contactgroup
 * @param region_id
 * @param country_id
 */
ncw.tpe.regions.countries.remove = function (region_id, country_id) {
    $.get(
        ncw.url(
            '/tpe/region/removeCountry/' + 
            region_id + '/' + country_id
        ), 
        null,
        function (data) {
            $('#ncw-added-country-' + country_id).remove();
            ncw.layout.table();
        },
        'json'
    ); 
};

ncw.tpe.regions();