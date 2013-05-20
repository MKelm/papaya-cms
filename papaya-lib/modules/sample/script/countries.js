$(document).ready(function() {

  var setupStateSelector = function(selCountry, selState) {
    urlParams = "?country=" + selCountry;
    if (selState != '') {
      urlParams += "&value=" + selState;
    }
    $.ajax({
      url: countryAjaxUrl + urlParams
    }).done(
      function(data) {
        str = '';
        $(data).find('state').each(
          function() {
            str += '<option value="' + $(this).attr('id') + '"';
            if ($(this).attr('selected') == 'selected') {
              str += ' selected = "selected"';
            }
            str += '>' + $(this).text() + "</option>\n";
          }
        );
        $('#stateSelector').html('');
        $('#stateSelector').append(str);
      }
    );
  }

  var setupCitySelector = function(selCountry, selState, selCity) {
    urlParams = "?country=" + selCountry + "&state=" + selState + "&cmd=list_cities";
    if (selCity != '') {
      urlParams += "&value=" + selCity;
    }
    $.ajax({
      url: countryAjaxUrl + urlParams
    }).done(
      function(data) {
        str = '';
        $(data).find('city').each(
          function() {
            str += '<option value="' + $(this).attr('id') + '"';
            if ($(this).attr('selected') == 'selected') {
              str += ' selected = "selected"';
            }
            str += '>' + $(this).text() + "</option>\n";
          }
        );
        $('#citySelector').html('');
        $('#citySelector').append(str);
      }
    );
  }

  if (selectedCountry != '') {
    setupStateSelector(selectedCountry, selectedState);
  }

  if (selectedCountry != '' && selectedState != '') {
    setupCitySelector(selectedCountry, selectedState, selectedCity);
  }

  $('#countrySelector').change(function() {
    setupStateSelector(
      $('#countrySelector :selected').val(),
      $('#stateSelector :selected').val()
    );
  });

  $('#stateSelector').change(function() {
    setupCitySelector(
      $('#countrySelector :selected').val(),
      $('#stateSelector :selected').val(),
      $('#citySelector : selected').val()
    );
  });
});
