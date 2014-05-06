<?php
/**
 *  Example script to use the Copernica SOAP API.
 *  @documentation  private
 *  @version        1.3
 */
require_once(__DIR__.'/soapclient.php');

/**
 *  Example PHP calls to the SOAP API
 */
try
{
    /**
     *  Credentials to access the Copernica SOAP API
     *  @var    string
     */
    $email = "your@email.com";
    $password = "yourpassword";

    /**
     *  The account name, it's also possible to set the account
     *  after a connection has been established with the SOAP API.
     *  @var    string | NULL
     */
    $account = null;
    
    /**
     *  SOAP API url
     *  This must be a valid hostname on which the server is reachable on.
     *  Default server:     http://soap.copernica.com
     *  @var    string
     */
    $url = "http://soap.copernica.com";
    
    /**
     *  Charset
     *  @var    string
     */
    $charset = "iso-8859-1";
    
    /**
     *  Should the script output what is being done?
     */
    $verbose = true;

    /**
     *  Instantiate SOAP api client
     */
    if ($verbose) echo("Make connection to SOAP environment\n");
    $soapclient = new PomSoapClient($email, $password, $account, $url, $charset);
    
    // show what we are doing
    if ($verbose) echo("Check if database already exists\n");
    
    // before we are going to construct a new database, we first check if there
    // is already a database with this name
    $database = $soapclient->Account_database(array(
        'identifier'    =>  'Fictional Persons 3'
    ));
    
    // Was an old database with the same name found?
    if (is_object($database))
    {
        // show what we are doing
        if ($verbose) echo("Remove old database\n");

        // remove the old database
        $soapclient->Database_remove(array(
            'id'        =>  $database->id
        ));
    }

    /**
     *  Example 1
     *  In this example we are going to create a database with a number of
     *  fields and a collection, and we will fill it with a number of 
     *  profiles. The database is created in the account 'DEMO'
     */

    // show what we are doing
    if ($verbose) echo("create database\n");

    // Create database with the name fictional persons
    $database = $soapclient->Account_createDatabase(array(
        'name'      =>  'Fictional Persons 3',
    ));
    
    // show what we are doing
    if ($verbose) echo("create field firstname\n");
    
    // Create field FirstName and it is displayed in the UI
    $soapclient->Database_createField(array(
        'id'                =>  $database->id,
        'name'              =>  'FirstName',
        'display'           =>  true,
    ));
    
    // show what we are doing
    if ($verbose) echo("create field lastname\n");
    
    // Create field LastName and it is displayed in the UI
    $soapclient->Database_createField(array(
        'id'                =>  $database->id,
        'name'              =>  'LastName',
        'display'           =>  true,
    ));
    
    // show what we are doing
    if ($verbose) echo("create field email\n");
    
    // Create field Email, it is displayed in the UI and to the values
    // of this field can send the emailing
    $soapclient->Database_createField(array(
        'id'                =>  $database->id,
        'name'              =>  'Email',
        'display'           =>  true,
        'specialcontent'    =>  'email',
    ));
    
    // show what we are doing
    if ($verbose) echo("create collection children\n");
    
    // create a collection in the database that holds a list with the names of
    // the children for each person in the database
    $collection = $soapclient->Database_createCollection(array(
        'id'                =>  $database->id,
        'name'              =>  'Children'
    ));
    
    // show what we are doing
    if ($verbose) echo("create collectionfield name\n");
    
    // there should be one field in the collection
    $soapclient->Collection_createField(array(
        'id'                =>  $collection->id,
        'name'              =>  'Name',
        'display'           =>  true
    ));
    
    // show what we are doing
    if ($verbose) echo("create profile piet papier\n");
    
    // Create the first profile in the main database
    $profile1 = $soapclient->Database_createProfile(array(
        'id'                =>  $database->id,
        'fields'            =>  array(
                                    'FirstName' =>  'Piet',
                                    'LastName'  =>  'Papier',
                                    'Email'     =>  'piet@papier.nl',
                                )
    ));
    
    // show what we are doing
    if ($verbose) echo("create subprofile paultje\n");
    
    // Piet has one child
    $child1 = $soapclient->Profile_createSubProfile(array(
        'id'                =>  $profile1->id,
        'collection'        =>  $soapclient->toObject(array(
                                    'id'    =>  $collection->id
                                )),
        'fields'            =>  array(
                                    'Name'  =>  'Paultje'
                                )
    ));
    
    // show what we are doing
    if ($verbose) echo("create profile ed emmer\n");
    
    // Create the second profile
    $profile2 = $soapclient->Database_createProfile(array(
        'id'                =>  $database->id,
        'fields'            =>  array(
                                    'FirstName' =>  'Ed',
                                    'LastName'  =>  'Emmer',
                                    'Email'     =>  'ed@emmer.nl',
                                )
    ));
    
    
    /**
     *  Example 2
     *  In this example we show how to retrieve information about a profile
     *  from the database, when you only know an e-mail address. If the
     *  profile is found, it will be updated: the e-mail address is changed,
     *  and a new child is added.
     */
     
     // show what we are doing
     if ($verbose) echo("search profile ed emmer\n");
   
    // Find the profile
    $profiles = $soapclient->Database_searchProfiles(array(
        'id'                =>  $database->id,
        'requirements'      =>  array(
                                    $soapclient->toObject(array(
                                        'fieldname'     =>  'Email',
                                        'value'         =>  'ed@emmer.nl',
                                        'operator'      =>  '='
                                    ))
                                )
    ));
    
    // update all profiles that were found
    foreach ($profiles->items as $profile)
    {
        // show what we are doing
        if ($verbose) echo("update profile ed emmer\n");
        
        // Change the e-mail address of the profile
        $soapclient->Profile_updateFields(array(
            'id'            =>  $profile->id,
            'fields'        =>  array(
                                    'Email'     =>  'other@address.nl'
                                )
        ));
        
        // show what we are doing
        if ($verbose) echo("add subprofile ingrid\n");
        
        // add a child to the profile
        $soapclient->Profile_createSubProfile(array(
            'id'            =>  $profile->id,
            'collection'    =>  $soapclient->toObject(array(
                                    'id'        =>  $collection->id
                                )),
            'fields'        =>  array(
                                    'Name'      =>  'Ingrid'
                                )
        ));
    }
}
catch (Exception $e)
{
    print_r($e);
}
