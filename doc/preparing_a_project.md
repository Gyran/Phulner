Preparing a Project
====================
Before Phulner can inject vulnerabilities the project has to be prepared. That is being done by inserting special keywords into the source files and creating a project config file.

Suggested Project Structure
------------------
This is the suggested file structure for a project. It's this structure that is assumed in the documents.
The Web Applications files should be putted in the `files` folder. In the `sanitationFuncions` folder your [Sanitation Funcions][sanitationFunctions] will reside.
```
.
├── files
│   ├── folder1
│   │   └── file1.php
│   ├── file2.php
│   └── changePassword.php
├── phulner.json
└── sanitationFunctions
    ├── function1.php
    └── function2.php
```


phulner.json
--------------------
This file describes the whole project and should be named `phulner.json` and reside in the project root. An example of a `phulner.json` is shown here:
```JSON
{
    "name": "name of project",
    "basedir": "files/",
    "vulnerabilities": {
        "xss_echoId": {
            "description": "Echoes the user supplied id",
            "type": "xss",
            "options": {
                "input": ["POST"],
                "sanitation": ["NONE", "BLACKLIST"],
                "output": ["NORMAL_TAG"],
                "mutated": [false]
            },
            "files": [
                "file2.php"
            ],
            "sanitationFunctions": [
                "sanitationFunctions/function1.php"
            ]
        },
        "csrf_changePassword": {
            "description": "Protects the change password action",
            "type": "csrf",
            "options": {
                "types": ["NONE", "ONLY_POST", "COMPUTABLE"]
            },
            "files": [
                "changePassword.php"
            ]
        }
    }
}
```
- __basedir__ This is the base of the web application. If you specify the file _file2.php_ Phulner will look at ___basedir___/_file2.php_.
- __vulnerabilities__ Is a object that describes all the vulnerabilities that can be injected into the project. Depending on the type, the keys in this might vary but these are the ones that always should be present.
    - __type__ The type the vulnerability is, currently `xss` and `csrf` is implemented.
    - __options__ Depending on the type, different options is supplied
    - __files__ The files Phulner has to look in the inject the vulnerablitiy.
    - __description__ The description of the vulnerability.

### XSS
If the type is `xss` these are the fields that should be in `options`:
- __input__ Array of where the vulnerability will get it's data
- __sanitation__ Array of how the sanitation can vary.
- __output__ Array of where the vulnerability will be outputted
- __mutated__ Array of if the data will be mutated

The key `sanitationFunctions` is an array of files that shall be included when importing the [Sanitation Functions][sanitationFunctions].

### CSRF
If the type is `csrf` these are the fields that should be in `options`:
- __types__ Array of the different types the CSRF vulnerability can vary in.

Preparing the source code
-------------------------
For Phulner to know where to inject the vulnerability some keywords has to be inserted into the source code. This is how every block of code that Phulner will use looks:
```PHP
/*Phulner
{
    "identifier": "xss_echoId",
    ...
}
*/
PHP code
/*/Phulner*/
```
The first keyword `/*Phulner` has to be in the beginning of a new line, then a JSON object describing this block and the configuration is ended with the keyword `*/`. Then anything to the last keyword, `/*/Phulner*/` on a new line is the code Phulner will analyze and inject the vulnerability in. The configuration object is different depending on the type of the vulnerability, but the `identifier` key has to be present. This identifier links the vulnerability together with all the blocks with the same identifier and to the vulnerability in `phulner.json`.

### XSS
When preparing a XSS vulnerability the `sanitation` key has to be present, it's an array of the possible sanitations the vulnerability can vary in. It also has to include a `initialScope`, how the scope looks when Phulner enters the block. Each element in the `initialScope` key represents an variable in the scope. It can be either of the type `variable` or `array`. The `name` represents the variable name and `taint` is what taint the variable has when entering the block.
If the type is `array`, two additional keys shall be present: `inherit` and `keys`.
- __keys__ An array of the keys in the array, it follows the same patterns as the `initialScope` key.
- __inherit__ If this is set to `true` the taint from the array will be inherited to all the keys not specified in the `keys` key.

An example of how source code prepared for injecting a XSS vulnerability looks:

```PHP
/*Phulner
{
    "identifier": "xss_echoId",
    "sanitation": ["NONE", "BLACKLIST"];
    "initialScope": [
        {
            "name": "userinput",
            "type": "variable",
            "taint": ["USER"]
        },
        {
            "name": "_POST",
            "type": "array",
            "taint": ["USER"],
            "inherit": true,
            "keys": [
                {
                    "name": "safe",
                    "type": "variable",
                    "taint": []
                }
            ]
        }
    ]
}
*/
$id = intval($userinput);
/*/Phulner*/
echo $id;
```

### CSRF

 [sanitationFunctions]: ../doc/sanitation_functions.md
