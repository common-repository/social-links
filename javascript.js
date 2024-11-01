
function social_links_ajax_saveOrder(){
	$('sortOrderData').value = Sortable.serialize("displayDiv");
}

function onTextKeyDown(){
	if(event.keyCode == 13){
		social_links_ajax_addNetwork(
			document.getElementById('networkDropdown').selectedIndex - 1,
			document.getElementById('addSettingInput'),
			document.getElementById('responseDiv')
		);
	}
}

function onDropToTrash(){
	var span = $('trash').firstChild.nextSibling;
	if(span){
		social_links_ajax_delete_network(span.id.split('_')[1]);
		span.parentNode.removeChild(span);
	}
	
}

function addLoadingIcon(){
	var img = Builder.node('span',{
						id:'loadingImage',
						className:'linkWrapper',
						title:'Loading',
						style:'position:relative;'},
						Builder.node('img',{src:'/wp-content/plugins/social-links/images/ajax-loader.gif',style:'margin:2px'},''));
	//console.log(img);
	$('displayDiv').appendChild(img);
}

function imageSelected(icon){

   var selectedIcon = $("selectedIcon");
   selectedIcon.src = icon.src
   selectedIcon.style.visibility = "visible";

}

function social_links_ajax_addNetwork(){
   var icon = $("selectedIcon").src;
   var url = $("url");
   var display = $("display");
   
   if(!inputValid(icon,url,display)){
      return;
   }

   icon = getFilename(icon);
   
	addLoadingIcon();

 	var mysack = new sack(getWordpressBaseLocation()+"wp-admin/admin-ajax.php" );    
	
	//console.log('Adding network '+siteID+ ': '+textInput);
	
	mysack.execute = 1;
	mysack.method = 'POST';
	mysack.setVar( "action", "social_links_add_network" );
	mysack.setVar( "icon", icon );
	mysack.setVar( "url", url.value );
	mysack.setVar( "displayname", display.value );
	mysack.encVar( "cookie", document.cookie, false );
	mysack.onError = function() { alert('Ajax error while adding new network' )};
	mysack.runAJAX();
	
	
  	return true;

}

function inputValid(icon,url,display){
   $("message").style.visibility = "hidden";
   if(!icon || url.value.length == 0 || display.value.length == 0){
      $("message").innerHTML = "Please select an icon, and fill all information.";
      $("message").className="updated fade";
      $("message").style.visibility = "visible";
      return false;
   }
   return true;
}

function social_links_ajax_delete_network(linkId){
	
	var mysack = new sack(getWordpressBaseLocation()+"wp-admin/admin-ajax.php" );    

	mysack.execute = 1;
	mysack.method = 'POST';
	mysack.setVar( "action", "social_links_delete_network" );
	mysack.setVar( "linkId", linkId );
	mysack.encVar( "cookie", document.cookie, false );
	mysack.onError = function() { alert('Ajax error while adding new network' )};
	
	mysack.runAJAX();
	
	//createSortables();
	
	return true;
}

function social_links_ajax_migrateData(){

 var mysack = new sack(getWordpressBaseLocation()+"wp-admin/admin-ajax.php" );

	//console.log('Adding network '+siteID+ ': '+textInput);

	mysack.execute = 1;
	mysack.method = 'POST';
	mysack.setVar( "action", "social_links_migrate" );
	mysack.encVar( "cookie", document.cookie, false );
	mysack.onError = function() { alert('Ajax error while adding new network' )};
	mysack.runAJAX();


  	return true;

}

function createSortables(){
	Sortable.destroy('displayDiv');
	Sortable.destroy('trash');

	targets = $$('.drop_target');
		Sortable.create('trash',{tag:'span',containment:targets,constraint:false,dropOnEmpty:true,
		onUpdate: function(){
			onDropToTrash();
		}
	});
	Sortable.create('displayDiv',{tag:'span',containment:targets,overlap:'horizontal',constraint:false});

}

function getWordpressBaseLocation(){
   var callBack = $('callBackUrl').value;
   return callBack.split('wp-content/')[0];
}

function getFilename(str) {
var slash = '/'
if (str.match(/\\/)) {
      slash = '\\'
}
sURL = str.substring(str.lastIndexOf(slash) + 1, str.length)
return sURL
}
   
