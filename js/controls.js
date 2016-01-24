/**
 * nettoie un url saisie par l'utilisateur.
 * @author FLO
 * @since 02/2006
 * @version 30/09/2006
 */
function cleanUrl(url)
{
	debug=false;
	
	// passage en minuscule
	url = url.toLowerCase();
	
	// test de l'existence du scheme
	var filtre  = new RegExp("^([a-z]+://)?([a-z0-9\._-]+)(/[a-z0-9\&amp;%_\./-~-]*)?");
	if (!filtre.test(url)) return url;
	else {
		var matches = filtre.exec(url);
		
		// debug		
		if (debug) {
			var alertString = matches.length+' pièces trouvées :\n';
			for (var i=0; i<matches.length; i++) {
				alertString += matches[i];
				alertString += '\n';
			}
			alert(alertString);
		}
		
		// construction du nouveau url
		newUrl = matches[1]==undefined ? 'http://' : matches[1]; // url scheme
		newUrl += matches[2]; // domain name
		if (matches[3]!=undefined) newUrl += matches[3]; // repertoires, nom de fichier et paramètres
		return newUrl;
	}
}
/**
 * Vérifie que l'url saisie dans un champ comporte un scheme, et affiche un lien correspondant à cette url.
 * @param string input_id l'id de l'élément <input>
 * @param string a_id l'id de l'élément <a>
 * @since 24/09/2006
 */
function checkUrlInput(input_id, a_id)
{
	var input = document.getElementById(input_id);
	var a = document.getElementById(a_id);
	var url = input.value;
	if (url) {
		//alert(url);
		url = cleanUrl(url);
		input.value = url;
		a.setAttribute('href', url);
		a.style.display='inline';
	}
}
/**
 * teste si <chaineDate> est une date valide (JJ/MM/AAAA ou JJ/MM/AA).
 * @return boolean 
 * @author May-Djoua
 * @since 21/07/2005
 */
function isDate(chaineDate)
{
    if (chaineDate == "") // si la variable est vide on retourne faux 
        return false; 
     
    e = new RegExp("^[0-9]{1,2}\/[0-9]{1,2}\/([0-9]{2}|[0-9]{4})$"); 
     
    if (!e.test(chaineDate)) // On teste l'expression régulière pour valider la forme de la date 
        return false; // Si pas bon, retourne faux 
  
    // On sépare la date en 3 variables pour vérification, parseInt() converti du texte en entier 
    j = parseInt(chaineDate.split("/")[0], 10); // jour 
    m = parseInt(chaineDate.split("/")[1], 10); // mois 
    a = parseInt(chaineDate.split("/")[2], 10); // année 
  
    // Si l'année n'est composée que de 2 chiffres on complète automatiquement 
    if (a < 1000) { 
        if (a < 89)    a+=2000; // Si a < 89 alors on ajoute 2000 sinon on ajoute 1900 
        else a+=1900; 
    } 
  
    // Définition du dernier jour de février 
    // Année bissextile si annnée divisible par 4 et que ce n'est pas un siècle, ou bien si divisible par 400 
    if (a%4 == 0 && a%100 !=0 || a%400 == 0) fev = 29; 
    else fev = 28; 
  
    // Nombre de jours pour chaque mois 
    nbJours = new Array(31,fev,31,30,31,30,31,31,30,31,30,31); 
  
    // Enfin, retourne vrai si le jour est bien entre 1 et le bon nombre de jours, idem pour les mois, sinon retourn faux 
    return ( m >= 1 && m <=12 && j >= 1 && j <= nbJours[m-1] ); 
}
/**
 * teste si <input> est un email valide.
 * @return boolean 
 * @author FLO
 * @since 02/2006
 */
function isEmail(input)
{
	//var filtre = new RegExp("^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9])+$");
	var filtre = new RegExp("\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b");
	alert(input+' is Email ?'+ filtre.test(input) ? 'oui' : 'non');
	return filtre.test(input);
}
/**
 * permet de décocher toutes les checkboxes dont le nom est passé en paramètre.
 * @since 23/08/2005
 * @author FLO
 */
function uncheck(checkboxes_name)
{
	var checkboxes = document.getElementsByName(checkboxes_name);
	//alert(checkboxes.length+' checkboxes a décocher');
	for (var i=0; i<checkboxes.length; i++) {
		checkboxes[i].setAttribute('checked', false); // pour IE
		checkboxes[i].removeAttribute('checked');
	}
}
/**
 * permet de cocher toutes les checkboxes dont le nom est passé en paramètre.
 * @since 23/08/2005
 * @author FLO
 */
function check(checkboxes_name)
{
	var checkboxes = document.getElementsByName(checkboxes_name);
	//alert(checkboxes.length+' checkboxes a cocher');	
	for (var i=0; i<checkboxes.length; i++) {
		checkboxes[i].setAttribute('checked', 'checked');
	}
}
