#!/usr/bin/env php
<?php
/**
 * Fetch metadata feeds, and split them into individual XML files for each entityID.
 */

require_once('vendor/autoload.php');

use Dataporten\MetadataImport\MetaEngine;

$me = new MetaEngine();
$me->process();



//
//
// function refeds_rns(SAML2_XML_md_EntityDescriptor $entity) {
//     $rns_attrs = array(
//         'urn:oid:1.3.6.1.4.1.5923.1.1.1.6' => 'eduPersonPrincipalName',
//         'urn:oid:1.3.6.1.4.1.5923.1.1.1.10' => 'eduPersonTargetedID',
//         'urn:oid:0.9.2342.19200300.100.1.3' => 'mail',
//         'urn:oid:2.16.840.1.113730.3.1.241' => 'displayName',
//         'urn:oid:2.5.4.42' => 'givenName',
//         'urn:oid:2.5.4.4' => 'sn',
//         'urn:oid:1.3.6.1.4.1.5923.1.1.1.9' => 'eduPersonScopedAffiliation',
//     );
//
//     if (empty($entity->Extensions)) { // no extensions
//         return;
//     }
//
//     if ($entity->Extensions instanceof SAML2_XML_Chunk) { // we have an XML chunk, skip
//         return;
//     }
//
//     // we have an array of extensions, iterate over them
//     foreach ($entity->Extensions as $extension) {
//         if (!$extension instanceof SAML2_XML_mdattr_EntityAttributes) {
//             // this is not the entity attributes extension, skip
//             continue;
//         }
//
//         // this is an EntityAttributes extension, iterate over attributes
//         foreach ($extension->children as $attribute) {
//             if (!$attribute instanceof SAML2_XML_saml_Attribute) {
//                 // not an attribute
//                 continue;
//             }
//
//             if (empty($attribute->AttributeValue)) { // empty value
//                 continue;
//             }
//
//             foreach ($attribute->AttributeValue as $value) {
//                 if (!$value instanceof SAML2_XML_saml_AttributeValue) {
//                     // not a valid attribute value
//                     continue;
//                 }
//
//                 if ($value->getString() === 'http://refeds.org/category/research-and-scholarship') {
//                     // this is a REFEDS R&S entity, do our stuff
//                     if (empty($entity->RoleDescriptor)) { // no roles, skip
//                         continue;
//                     }
//
//                     foreach ($entity->RoleDescriptor as $role) {
//                         if (!$role instanceof SAML2_XML_md_SPSSODescriptor) {
//                             continue;
//                         }
//
//                         // this is an SP claiming the R&S entity category, check attributes and add what's needed
//
//                         if (empty($role->AttributeConsumingService)) {
//                             $acs = new SAML2_XML_md_AttributeConsumingService();
//                             $acs->ServiceName = array('en' => 'Unknown');
//                             $acs->ServiceDescription = array('en' => 'Unknown');
//                             $acs->index = 0;
//                             if (!empty($role->Extensions)) {
//                                 foreach ($role->Extensions as $rext) {
//                                     if (!$rext instanceof SAML2_XML_mdui_UIInfo) {
//                                         continue;
//                                     }
//
//                                     $acs->ServiceName = $rext->DisplayName; // DisplayName is mandatory for R&S
//                                     $acs->ServiceDescription = $rext->Description;
//                                 } // SPSSODescriptor extensions
//                             }
//                             $role->AttributeConsumingService = array($acs);
//                         }
//
//                         $missing_req_attrs = $rns_attrs;
//                         foreach ($role->AttributeConsumingService as $racs) {
//                             // remove attributes that are already requested from the list of attributes to add
//                             foreach ($racs->RequestedAttribute as $req_attr) {
//                                 if (array_key_exists($req_attr->Name, $missing_req_attrs)) {
//                                     unset($missing_req_attrs[$req_attr->Name]);
//                                 }
//                             }
//
//                             // add R&S attributes to the list of requested attributes
//                             foreach ($missing_req_attrs as $name => $friendly) {
//                                 $ra = new SAML2_XML_md_RequestedAttribute();
//                                 $ra->Name = $name;
//                                 $ra->FriendlyName = $friendly;
//                                 $ra->NameFormat = SAML2_Const::NAMEFORMAT_URI;
//                                 $racs->RequestedAttribute[] = $ra;
//                             }
//                         } // attribute consuming services
//                     } // role descriptors
//                 }
//             } // attribute values
//         } // attributes
//     } // extensions
// }
