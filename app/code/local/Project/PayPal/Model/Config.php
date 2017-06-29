<?php
class Project_Paypal_Model_Config extends Mage_Paypal_Model_Config
{
 
    /**
     * BN code getter
     * override method
     *
     * @param string $countryCode ISO 3166-1
     */
    public function getBuildNotationCode($countryCode = null)
    {
        //$newBnCode = 'NextITLLC_SI_Custom';
        //if you would like to retain the product and country code
        //E.g., Company_Test_EC_US
        $bnCode = parent::getBuildNotationCode($countryCode);
        $newBnCode = str_replace('ClassyLlama_SI_MagentoEE_RefTran','NextITLLC_SI_Custom',$bnCode);
        return $newBnCode;
    }
 
}
?>