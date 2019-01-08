<?php
namespace Application\Document;

class Authorizable extends Model
{

    public $role;

    public $username;

    public function addUserToOrganization($userNameEmail, $role)
    {
        $m = new \MongoClient();
        $mongoObjectFactory = new \Application\Controller\MongoObjectFactory();
        
        $dbmain = $m->{$mongoObjectFactory->getDBName("Organization")};
        $organizationTable = $dbmain->selectCollection("organization");
        if (isset($_SESSION['organization'])) {
            $organization = $organizationTable->findOne(array(
                'name' => $_SESSION['organization']
            ));
            $organization->addUser($userNameEmail, $role);
        }
      
    }
}

?>