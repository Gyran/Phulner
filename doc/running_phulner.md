Running Phulner
===================
After a project has been prepared Phulner can inject vulnerabilities for that project.

Create an instance config
--------------------------
The instance config file describes how the generated web application shall be vulnerable.

```JSON
{
    "project": "/path/to/prepared/phulner/project/",
    "out": "/destination/of/vulnerable/web/application/",
    "vulnerabilities": {
        "xss_echoId": {
            "inject": true,
            "sanitation": "NONE"
        },
        "csrf_changePassword": {
            "inject": true,
            "type": "NONE"
        }
}
```
- __project__ The path to the prepared project. There should be a `phulner.json` file in this directory.
- __out__ Where the web application with the injected vulnerabilities will be generated.
- __vulnerabilities__ An object with the vulnerabilities that shall be injected. The key shall correspond to the `identifier` in `phulner.json`. Depending on the vulnerability different keys will be in this object.
    - __inject__ This key has to be present. Will determine if the vulnerability shall be injected or not.
    - ___other___ Other keys is depending on the vulnerability that shall be injected. They usually correspond to determine which category the injected vulnerability shall be in.
