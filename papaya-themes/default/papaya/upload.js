// Javascript program to control an upload progress bar
// Papaya Dimensional GmbH Steve Dix May 1st 2007
//
// Tested successfully with the following browsers :
// Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3
// Opera/9.20 (Windows NT 5.0; U; de)
// Microsoft Internet Explorer 6 (sp1)
// Explorer 7 / Windows Vista
// Firefox 2.0.0.3 / Windows Vista
// Opera 9.20 / Windows Vista
// Firefox 2.0.0.2 / Linux
// Opera 9.20 / Linux
// Konqueror 3.5.6 / Linux
// Firefox 2.0.0.2 / Apple System X
// Opera 9.10 / Apple System X
//
// The following do not work :
// Apple System X Safari 2.0.4 - unknown why xmlrpc fails.


var xmlhttp;                 // container variable for XMLRPC class.
var explorer;                // boolean for microsoft support
var id;                      // randomly generated ID that must be changed every time
var normaltime = 500;        // update interval : 1/2 sec : I could go as high as 1/10th, but
                             // updating more than two times a second isn't noticable to the user.
                             // furthermore, it will probably scale better for heavy upload traffic
                             // if we're not demanding XML from the upload server ten times every second.
var waittime = 1000;         // initial wait timeout to give the upload serverside script time to generate upload stats.

var filename = '';           // filename of the file being uploaded.  Not strictly required, but nice to have.

var microsofthttp;           // version of active X object supported
var uploading = -1;
var totalmax='';

// special list of the various microsoft ActiveXobjects that replace the standard call.
var XMLHTTP = new Array('MSXML2.XMLHTTP.5.0','MSXML2.XMLHTTP.4.0',
  'MSXML2.XMLHTTP.3.0','MSXML2.XMLHTTP','Microsoft.XMLHTTP' );

// convenient equivalent of php str_replace
// used to translate angle brackets into html &chars.
function str_replace(search, replace, subject) {
    return subject.split(search).join(replace);
}

// take xml and display it in the debug div
// after converting it so that angle brackets will be displayed
function XMLdump(XML) {
  var settings = '';
  if (explorer == true) {
    settings='ActiveXobject Version : ' + microsofthttp + '<br/>';
  } else {
    settings='Standard DOM-XMLRPC<br/>';
  }

  XML = str_replace('<', '&lt;', XML);
  XML = str_replace('>', '&gt;', XML);
  document.getElementById("debug").innerHTML = settings + "<pre>" + XML + "</pre>";
}

// switch off the submit button on the
// form so that the user can't click it
// and mess up the upload by restarting it.
function disableSubmit(whichButton) {
  if (document.getElementById)  // Mozilla style
  {
      document.getElementById(whichButton).disabled = true;
  }
  else if (document.all)        // Microsoft style
  {
      document.all[whichButton].disabled = true;
  }
  else if (document.layers)     // I don't think we really need support NS4, but we might as well leave it.
  {
      document.layers[whichButton].disabled = true;
  }
}

// create a unique ID every time the program starts.
function uniqueid() {
  var hex = "0123456789abcdef";
  var chr = '';     // buffer for the random hex string
  for (var i = 0; i < 32; i++)   {
    unround = Math.random() * 16;
    rnd     = Math.floor(unround);    // create random no 1-16
    chr     = chr + hex.charAt(rnd);  // hex char
  }
  uid = chr;   // set the upload ID ready for the hidden field
  return uid;
}

// do a remote xmlrpc call to the server
function loadXMLDoc(myurl,async) {
  // Microsoft requires that we reinitialise every time.
  if (explorer) {
    xmlhttp = new ActiveXObject(microsofthttp);
    // microsoft supplies several different versions of the XMLHTTP activeXobject
    // for backwards (un)compatibility, which means that just invoking Microsoft.XMLHTTP
    // doesn't work on Externet Inplorer 7, because it isn't supported any more..
    // So we must test for the valid version, and then store that in the explorer variable.
  }

  // set up a function which is called when we detect a change in state.
  xmlhttp.onreadystatechange = statechange;

  xmlhttp.open("GET", myurl, async);
  xmlhttp.send(null);         // actually do the communication.
}

// function called by the xmlrpc system every time there is a
// state-change in the http link.
function statechange() {
  var ret = false;        // failure assumed until we know otherwise.

  // if xmlhttp shows "loaded"
  if (typeof xmlhttp != 'undefined' && xmlhttp.readyState == 4) {
    // alert(xmlhttp.responseText);
    // this has to be tested separately otherwise it causes errors on some browsers.
    if (xmlhttp.status == 200) {
      if (debug == 1) { // display the xml in the document
                        // left here for future dev, such as getting Safari to work
        XMLdump(xmlhttp.responseText);
      }

      if (explorer) {                // microsoft has to be different
        var doc = new ActiveXObject("Microsoft.XMLDOM");
        doc.async = "false";
        doc.loadXML(xmlhttp.responseText);
      } else {       // code for Mozilla, Firefox, Opera, etc.
        var parser = new DOMParser();
        var doc = parser.parseFromString(xmlhttp.responseText,"text/xml");
      }

      // pull the information out of the xml document.
      var percent = doc.getElementsByTagName("percent")[0].childNodes[0].nodeValue;
      var message = doc.getElementsByTagName("message")[0].childNodes[0].nodeValue;
      var eta     = doc.getElementsByTagName("eta")[0].childNodes[0].nodeValue;
      var total   = doc.getElementsByTagName("total")[0].childNodes[0].nodeValue;
      var upl     = doc.getElementsByTagName("upl")[0].childNodes[0].nodeValue;
      var speed   = doc.getElementsByTagName("speed")[0].childNodes[0].nodeValue;

      // build the progress data line and display it in the status div
      if(percent>uploading) {    // if the upload is proceeding (ie the percent is bigger than last time
        uploading=percent;      // just copy to the uploading variable
        totalmax = total;   // save the total as it gets messed up by the 0 percent
      }
      
      if(percent<uploading && percent==0) {    // if we were uploading but the percentage goes to 0
        percent=100;                          // then we must have finished uploading.
        document.getElementById("status").innerHTML = filename + '<br/>' + 
          percent + "% &nbsp;(" + totalmax + " of " + totalmax + ")&nbsp;&nbsp;&nbsp;Speed:" + speed +
          "&nbsp;&nbsp;&nbsp;<br/>\nUpload Completed";
        // note we don't bother to set a timeout call after we find we've uploaded.
      } else {
        document.getElementById("status").innerHTML = filename + '<br/>' + percent +
          "% &nbsp;(" + upl + " of " + total + ")&nbsp;&nbsp;&nbsp;Speed:" + speed +
          "&nbsp;&nbsp;&nbsp;Time remaining:"+ eta + "<br/>\n" + message;

        var timer = setTimeout('callevery()', normaltime);
      }
      
      // stretch the progress bar
      document.getElementById('progress').style.width = percent + '%';


      ret = true;
    }
  }
  // note that we do not trap comms errors : they are largely non-existent
  // and due to the messy way that microsoft works, we often fall out here
  // with a ready state != 4, meaning it's still loading the document.
  return ret;
}

// dummy function used to repeatedly call timeouts.
function callevery() {
  // call the xml reading function for reading asynchronously.
  loadXMLDoc(url, true);
}

function submission(subbutton) {
  id=uniqueid();
  disableSubmit(subbutton);
  start();
  //document.uploader.submit();  // Note well, this submit is actually not needed - the form does an implicit submit.
  id=uniqueid();                 // this was the cause of problems in IE 6 Service Pack 1
  return true;
}

function start()
{
  // sounding-out browser stuff moved to here so it's done only once
  xmlhttp = null;

  explorer = false;
  for (var i = 0; i < XMLHTTP.length && !explorer; i++) {
    try {
      // see above for explanation of why microsoft can't keep things simple.
      microsofthttp = XMLHTTP[i];         // cache the type to save time
      xmlhttp       = new ActiveXObject(microsofthttp);
      explorer      = true;
    } catch (e) {}
  }
  if (!explorer && window.XMLHttpRequest) {  // code for Mozilla, Safari, etc.
    xmlhttp = new XMLHttpRequest();
  }

  if (xmlhttp != null) {        // if we have successfully created an xml request object
    document.uploader.UPLOAD_IDENTIFIER.value = id;     //set the id of the upload
    url = url + id;
    filename = document.getElementById("upload").value;          // get the filename : largely cosmetic
    filename = str_replace('\\','/',filename);      // replace forward slashes with backslash, if any.
    if (filename.lastIndexOf('/') > 0) {                // get just the name off the filename for those browsers who use the whole path.
      filename = filename.substring(filename.lastIndexOf('/') + 1);
    }

    // display an initial message, rather than just leaving the bar blank before the initial update is received.
    document.getElementById("status").innerHTML
      = filename + '<br/><br/>Establishing Connection...please wait...';

    // if we get here, then we know that the browser supports
    // xml fetch, so we can reveal the status bar div and start the
    // javascript.

    // set the bar to nothing.
    document.getElementById('bar').style.display = '';

    // start the timer that will service the bar.
    var timer = setTimeout('callevery()', waittime);
    // if we don't find xml support, then the browser
    // gracefully degrades to the normal upload.  Hopefully.
  }
}
//****end of file.
