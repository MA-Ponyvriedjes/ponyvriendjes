function F2C_GeoCoderConvertAddress(prefix) 
{
	if (geocoder) 
	{
		var localmap;
		var geocoderstatus = false;
		var address = document.getElementById(prefix+'_address').value;
	
		geocoder.geocode( { 'address': address}, function(results, status) 
		{
			if (status == google.maps.GeocoderStatus.OK) 
			{
				geocoderstatus = true;
				F2C_ClearMarker(prefix);				
				document.getElementById(prefix+'_latlon').innerHTML = '(' + results[0].geometry.location.toUrlValue(5) + ')';
				document.getElementById(prefix+'_hid_lat').value = results[0].geometry.location.lat().toFixed(5);
				document.getElementById(prefix+'_hid_lon').value = results[0].geometry.location.lng().toFixed(5);
				document.getElementById(prefix+'_error').style.display = 'none';
			   
				eval('localmap='+prefix+'_map;');
				
				if(localmap)
				{
					eval(prefix+'_marker = new google.maps.Marker({ map: localmap, position: results[0].geometry.location, draggable: true });');
					var marker = eval(prefix+'_marker');
					
					localmap.setCenter(results[0].geometry.location);
					
					google.maps.event.addListener(marker, "dragend", 
					function()
					{
						var position = marker.getPosition();
						document.getElementById(prefix+'_latlon').innerHTML= '(' + position.toUrlValue(5) + ')';
						document.getElementById(prefix+'_hid_lat').value = position.lat().toFixed(5);
						document.getElementById(prefix+'_hid_lon').value = position.lng().toFixed(5);
					}
					);
				}
			}
			else
			{				
				F2C_GeoCoderClearResults(prefix);
				document.getElementById(prefix+'_address').value = address;
				document.getElementById(prefix+'_error').style.display = 'block';
			}
		});	  
	}
}

function F2C_GeoCoderClearResults(prefix)
{
	document.getElementById(prefix+'_latlon').innerHTML= '';
	document.getElementById(prefix+'_hid_lat').value = '';
	document.getElementById(prefix+'_hid_lon').value = '';	
	document.getElementById(prefix+'_address').value = '';
	document.getElementById(prefix+'_error').style.display = 'none';
	F2C_ClearMarker(prefix);
}
function F2C_ClearMarker(prefix)
{
	var localmarker;
	eval('localmarker='+prefix+'_marker;');
	if(localmarker) localmarker.setMap(null);
}