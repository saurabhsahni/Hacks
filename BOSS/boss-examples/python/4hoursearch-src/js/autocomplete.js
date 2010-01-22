// Copyright (c) 2008 Yahoo! Inc. All rights reserved.
// Licensed under the Yahoo! Search BOSS Terms of Use
// (http://info.yahoo.com/legal/us/yahoo/search/bosstos/bosstos-2317.html)
// An XHR DataSource
var myServer = "suggest";
var mySchema = ["Results", "v"];
var myDataSource = new YAHOO.widget.DS_XHR(myServer, mySchema);
// ...or configure the response type to be JSON (default)
myDataSource.responseType = YAHOO.widget.DS_XHR.TYPE_JSON;
myDataSource.scriptQueryParam = "q";

var myAutoComp = new YAHOO.widget.AutoComplete("myInput", "myContainer", myDataSource);

// Container will expand and collapse vertically
myAutoComp.animVert = true;

// Require user to type at least 3 characters before triggering a query
myAutoComp.minQueryLength = 3;

// Disable type ahead
myAutoComp.typeAhead = false;
myAutoComp.autoHighlight = false;

// Submit button
var oButton = new YAHOO.widget.Button(
        "searchbutton", {
    type: "submit",
    label: "Search"
});
