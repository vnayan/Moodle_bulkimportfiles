Resource Bulk Upload
===========================

Resource Bulk Upload is  a Moodle plugin to bulk upload resources to multiple courses.
Upload a zip file and plugin will generate a csv document. Enter category, course and section id in the csv document. 
On uploading CSV, files from the zip will be mapped to respective section of courses.

Features
--------

- Resource Bulk Upload- It upload the zip file of content(docs,pdf,video etc.) and generate a CSV files which contain the following information.
    
    - file name and file path url.
	
- Enter the categoryid,courseid,section number in the CSV document where the content needs to be uploaded. 


Instalation
-----------
- Extract the content into your {Moodle root directory}/admin/tool/uploadcontent.
- Go to Notifications page.
- Install the plugin and Enjoy.

Requirements
------------
- Moodle 3.0 onwards.


TODO
----
This plugin is in ALPHA state of development, so there are more improvements to come:

 - Scouce file can be located in any repository which will be pulled and mapped. 


License
-------

Licensed under the [GNU GPL License](http://www.gnu.org/copyleft/gpl.html).
