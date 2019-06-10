google.charts.load("current", {packages:["corechart"]});
google.charts.setOnLoadCallback(drawChart);

var filesData;  var filesChart; var filesOption;
var fileData;   var fileChart;  var fileOption;
var fileOk=0; var fileError=0;
var testOk=0; var testError=0;
var assertOk=0; var assertError=0;
var testFiles;

function drawChart() {
    filesData = google.visualization.arrayToDataTable([['Task', 'Files'],['',1]]);

    filesOption = {
        title: 'Unit Test files found:',
        pieHole: 0.4,
        colors: ['gray']
    };
    filesChart = new google.visualization.PieChart(document.getElementById('filesGauge'));
    function selectFilesHandler() {
        var selectedItem = filesChart.getSelection()[0];
        if (selectedItem) {
            aktTestNr=0;resetCounters();$("#console").empty();
            runTest(selectedItem.row);
        }
    }
    google.visualization.events.addListener(filesChart, 'click', selectFilesHandler);
    filesChart.draw(filesData, filesOption);

    fileData = google.visualization.arrayToDataTable([['Task','Tests' ],['',1]]);
    fileOption = {
        title: 'Asserts in current file:',
        allowHtml:true,
        pieHole: 0.4,
        colors: ['gray']
    };
    fileChart = new google.visualization.PieChart(document.getElementById('fileGauge'));
    function selectFileHandler() {
        var selectedItem = fileChart.getSelection()[0];
        if (selectedItem) {
            resetCounters();$("#console").empty();
            runTest(aktFileNr,selectedItem.row);
        }
    }
    google.visualization.events.addListener(fileChart, 'click', selectFileHandler);
    fileChart.draw(fileData, fileOption);

    <?php if(in_array($pu->getGetParam("action"),array("autorun","scanfiles")) || true) {?>
        getTestFiles();
    <?php }?>
}

function getTestFiles() {
    $.ajax({
        url:'ajaxGetTestFiles.php',
        type:'GET',
        success:function(data){
            testFiles=data;
            while (filesData.getNumberOfRows()>0)
                filesData.removeRow(0);
            var i=0;
            var tests=0;
            data.forEach(function(testFile) {
                filesOption.colors[i++]='blue';
                filesData.addRow([testFile.file,testFile.tests]);
                tests+=testFile.tests;
            });
            filesOption.title ='PhpUnit files:'+filesData.getNumberOfRows()+' Test:'+tests;
            filesChart.draw(filesData, filesOption );
        <?php if($pu->getGetParam("action")=="autorun") {?>
            runAlltests();
         <?php }?>

        },
        error:function(error) {
            alert('Error getting the list of test files!');
        }
    });
}

var aktFileNr=0;
var aktTestNr=0;
var aktFileError=false;

function resetCounters() {
    fileOk=0; fileError=0;
    testOk=0; testError=0;
    assertOk=0; assertError=0;
}

function runAlltests() {
    aktFileNr=0;aktTestNr=0;aktFileError=false;
    resetCounters();
    console.log(aktFileNr+'-'+aktTestNr);
    for(var i=0;i<filesData.getNumberOfRows();i++) {
        filesOption.colors[i] = 'blue';
    }
    $("#console").empty();
    runTest();
}

/**
 * Run Ajax Text
 * @param oneFileNr if set then only one file is to be tested
 * @param oneTestNr if set then only one test ist to be tested
 */
function runTest(oneFileNr,oneTestNr) {
    if (oneFileNr!=null) aktFileNr=oneFileNr;
    if (oneTestNr!=null) aktTestNr=oneTestNr;
    console.log("FileNr:"+aktFileNr+" TestNR:"+aktTestNr);

    var testFile=testFiles[aktFileNr];
    var result = Array;
    if (aktTestNr==0) {
        aktFileError=false;
        //Display testfile name
        fileOption.title ='Test file:'+testFiles[aktFileNr].file;
        //Delete file tests from pie diagram and add only one blue element
        while (fileData.getNumberOfRows()>0)
            fileData.removeRow(0);
        fileData.addRow(["",1]);
        fileOption.colors[0]="blue";
        fileChart.draw(fileData, fileOption);
    }
    $.ajax({
        url:'ajaxUnitTestRun.php?file='+testFile.file+"&dir="+testFile.dir+"&testNr="+aktTestNr,
        type:'GET',
        success:function(data){
            if (typeof(data.filestatus)==null || data.filestatus == "error") {
                setTextToConsole(data.errorMessage, 'red', true);
                filesOption.colors[aktFileNr] = 'red';
                aktTestNr = 0;
                aktFileNr++;
                fileError++;
                if (aktFileNr < filesData.getNumberOfRows() && oneFileNr == null) {
                    runTest(oneFileNr, oneTestNr);
                }
            } else {
                if (aktTestNr == 0 || oneTestNr != null) {
                    fileOption.title = 'Test file:' + testFiles[aktFileNr].file + '\nTest name:';
                    //Initialise the tests pie with blue elements
                    while (fileData.getNumberOfRows() > 0)
                        fileData.removeRow(0);
                    for (var i = 0; i < data.tests.length; i++) {
                        fileData.addRow([data.tests[i], 1]);
                        fileOption.colors[i] = "blue";
                    }
                }

                if (data.filestatus == "done") {
                    setTestResults(data);
                    if (!aktFileError) {
                        filesOption.colors[aktFileNr] = 'green';
                        fileOk++;
                    } else {
                        filesOption.colors[aktFileNr] = 'red';
                        fileError++;
                    }
                    filesChart.draw(filesData, filesOption );
                    aktTestNr = 0;
                    if (oneTestNr == null && oneFileNr == null) {
                        if (aktFileNr+1 < filesData.getNumberOfRows()) {
                            aktFileNr++;
                            runTest(oneFileNr, oneTestNr);
                        }
                    }

                }
                if (data.filestatus == "running") {

                    filesOption.colors[aktFileNr] = 'yellow';
                    filesChart.draw(filesData, filesOption );
                    setTestResults(data);
                    aktTestNr++;
                    if (oneTestNr == null)
                        runTest(oneFileNr, oneTestNr);
                }
            }
        },
        error:function(error) {
            setTextToConsole(error, 'red', true);
            filesOption.colors[aktFileNr] = 'red';
            fileError++;
            filesChart.draw(filesData, filesOption );
            aktTestNr = 0;
            aktFileNr++;
            if (aktFileNr < filesData.getNumberOfRows() && oneFileNr == null) {
                runTest(oneFileNr, oneTestNr);
            }
        }
    });
}

function setTestResults(data) {
    fileOption.title = 'Test file:' + testFiles[aktFileNr].file;//+'\nTest name:'+data.tests[aktTestNr].name;
    if (data.test == true) {
        if (data.assertOk>0) {
            $color = 'green';
        } else {
            $color = 'orange';
        }
        testOk++;
        assertOk += data.assertOk;
    } else {
        $color = 'red';
        testError++;
        aktFileError=true;
        assertOk += data.assertOk;
        assertError += data.assertError;
    }
    fileOption.colors[aktTestNr] = $color;
    fileChart.draw(fileData, fileOption);

    setTextToConsole(data.time + 'ms ' + data.testName, $color, true);

    if (data.echo.length>0) {
        setTextToConsole(data.echo,$color);
    }
    if (data.errorMessage!==null) {
        setTextToConsole(data.errorMessage,"red",true);
    }
    showResultCounters();
}

function setTextToConsole(text,color,newline) {
    if (color == null) color = "black";
    if (newline == null || newline == false)
        newline = "span";
    else
        newline = "div";
    var c= $('<'+newline+' style="color:'+color+'"></'+newline+'>');
    c.html(text);
    $("#console").append(c);
}

function showResultCounters() {
    $('#fok').text(fileOk);   $('#ferror').text(fileError);
    $('#tok').text(testOk);   $('#terror').text(testError);
    $('#aok').text(assertOk); $('#aerror').text(assertError);
}

function showCallStack(text) {
    text = text.replace(/<br\s*\/?>/mg,"\n");
    alert(text);
}
