<?php
/* SVN FILE: $Id$ */
/**
 * Contains the IndexController class.
 *
 * PHP Version 5
 * Copyright (c) 2009 Netzcraftwerk UG (haftungsbeschränkt)
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Lanzinger Medien.
 *
 * @author 			Winfried Weingartner <w.weingartner@netzcraftwerk.com>
 * @copyright		Copyright 2007-2008, Netzcraftwerk UG (haftungsbeschränkt)
 * @link			http://www.netzcraftwerk.com
 * @package			netzcraftwerk
 * @since			Netzcraftwerk v 3.0.0.1
 * @version			Revision: $LastChangedRevision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$LastChangedDate$
 * @license			http://www.netzcraftwerk.com/licenses/
 */
/**
 * IndexController class.
 *
 * @package netzcraftwerk
 */
class Tpepdb_IndexController extends Tpepdb_ModuleController
{

	/**
	 * The page title
	 *
	 * @var string
	 */
	public $page_title = "TPE PDB :: Overview";

	
    public $acl_publics = array(
        'importPgDatabase',
        'importSaveName',
        'importUpdateCompoundId'
    );
	
	/**
	 * No model used
	 *
	 * @var boolean
	 */
	public $has_model = false;

	/**
	 * Overview
	 *
	 */
	public function indexAction ()
	{

	}

    /**
     * Get Name and Developmentname of all compounds
     * in compound_document_table
     *
     */
    public function importSaveNameAction ()
    {
        $this->view = false;
        /* 
        $obj_compound_document = new Tpepdb_CompoundDocument();
        $obj_compound_document->unbindModel('all');
        $arr_compound_document = $obj_compound_document->fetch('all');
        
        $obj_compound = new Tpepdb_Compound();
        $obj_compound->unbindModel('all');

        $obj_compound_document_save = new Tpepdb_CompoundDocument();
        $obj_compound_document_save->unbindModel('all');
        foreach($arr_compound_document As $coumpound_document) {
            $obj_compound->setId($coumpound_document->getCompoundId());
            $obj_compound->read();
            echo '<br />' 
                . $coumpound_document->getId() 
                . ' compound_id=' 
                . $coumpound_document->getCompoundId() 
                . ' document_id=' 
                . $coumpound_document->getDocumentId() 
                . ' Compound Name=' 
                . $obj_compound->getName()
                . ' Compound Developmentname=' 
                . $obj_compound->getDevelopmentName();
                
           $obj_compound_document_save->setId($coumpound_document->getId());
           
           $obj_compound_document_save->setCompoundName($obj_compound->getName());
           $obj_compound_document_save->setCompoundDevelopmentName($obj_compound->getDevelopmentName());
           
           // save compound name and development name
           $obj_compound_document_save->saveField('compound_name');
           $obj_compound_document_save->saveField('compound_development_name');
        }*/
    }

    /**
     * Update compound_id in compound_document table
     * use development_name as index
     *
     */
    public function importUpdateCompoundIdAction ()
    {
        $this->view = false;
        $obj_compound_document = new Tpepdb_CompoundDocument();
        $obj_compound_document->unbindModel('all');
        $arr_compound_document = $obj_compound_document->fetch('all');
        
        
        foreach($arr_compound_document As $coumpound_document) {
            $obj_compound = new Tpepdb_Compound();
            $obj_compound->unbindModel('all');
            $arr_compound = $obj_compound->fetch(
                'all',
                array(
                    'conditions' => array(
                        'development_name' => $coumpound_document->getCompoundDevelopmentName()
                    )
                )
            );
           
            
            
            echo '<br />' 
                . $coumpound_document->getId() 
                . ' compound_id=' 
                . $arr_compound[0]->getId()
                . ' document_id=' 
                . $coumpound_document->getDocumentId() 
                . ' Compound Name=' 
                . $coumpound_document->getCompoundName()
                . ' Compound Developmentname=' 
                . $coumpound_document->getCompoundDevelopmentName();
                
           $obj_compound_document_save = new Tpepdb_CompoundDocument();
           $obj_compound_document_save->unbindModel('all');
           
           // set the id of compound_document
           $obj_compound_document_save->setId($coumpound_document->getId());
           // set compound_id
           $obj_compound_document_save->setCompoundId($arr_compound[0]->getId());
           // save_compound_id
           $obj_compound_document_save->saveField('compound_id');
        }

        echo '<br />update compoundid done.';
        
        $obj_settings_save = new Tpepdb_Setting();
        $obj_settings_save->unbindModel('all');
        $obj_settings_save->setId(1);
        $obj_settings_save->setLatestupdatetime(date('Y-m-d H:i:s'));
        if (true == $obj_settings_save->saveField('latestupdatetime')) {
            echo ' Save OK time=' . date('Y-m-d H:i:s');
        }
        
    }


	/**
	 * Import Data
	 *
	 */
    public function importPgDatabaseAction ()
    {
	    /*  	
	     * following attributes must not be imported
		# Rest elongation
		# Melting temperature
		# Residence dwell time at provessing temperature
		# Viscosity at processing temperature
		# Electrical properties
		*/
        $this->view = false;
		
		// Delete all cached pdfs
        $path = ASSETS . DS . 'tpepdb' . DS . 'compound_pdfs' . DS;
        if (true === file_exists($path)) {
            $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);
            for ($dir->rewind(); $dir->valid(); $dir->next()) {
                if ($dir->isDir()) {
                    rmdir($dir->getPathname());
                } else {
                    unlink($dir->getPathname());
                }
            }
        }	

        ob_start();
        /*
        $db = new PDO(
            'mysql:dbname=kraiburg-tpe-oldpdb;host=localhost;',
            'root',
            'root'
        );
        */
        $db = new PDO(
            'mysql:dbname=kraibn_db4;host=localhost;',
            'kraibn_4',
            'S2n4h3vKncw'
        );
        
        /*
        $db2 = new PDO(
            'mysql:dbname=kraiburg-tpe3;host=localhost;',
            'root',
            'root'
        );
        */
        
        $db2 = new PDO(
            'mysql:dbname=kraibn_db3;host=localhost;',
            'kraibn_3',
            'E1FzBvjAncw'
        );
        
        // TRUNCATE
        $db2->query(
           '
            TRUNCATE ncw_tpepdb_compound;
            TRUNCATE ncw_tpepdb_compound_adhesion;
            TRUNCATE ncw_tpepdb_compound_attribute;
            TRUNCATE ncw_tpepdb_compound_burningrate;
            TRUNCATE ncw_tpepdb_compound_certification;
            TRUNCATE ncw_tpepdb_compound_conformity;
            TRUNCATE ncw_tpepdb_compound_hotairageing;
            TRUNCATE ncw_tpepdb_compound_mediaresistance;
            TRUNCATE ncw_tpepdb_compound_processingmethod;
            TRUNCATE ncw_tpepdb_compound_remark;
            TRUNCATE ncw_tpepdb_compound_series;
            TRUNCATE ncw_tpepdb_compound_weathering;
           '
        );
        
        // out
        // TRUNCATE ncw_tpepdb_safetydata;
        // TRUNCATE ncw_tpepdb_safetydata_file;
        // TRUNCATE ncw_tpepdb_msds;
        // TRUNCATE ncw_tpepdb_msds_file;
        // TRUNCATE ncw_tpepdb_compound_document;
        
        $db2->query(
            '
            TRUNCATE ncw_tpepdb_series;
            TRUNCATE ncw_tpepdb_processingset;
            TRUNCATE ncw_tpepdb_processingset_file;
            '
        );
        // out
        // TRUNCATE ncw_tpepdb_document;
        // ;
        //
        
       // var_dump($db2->errorInfo());

        $this->loadModel('Series');
        $this->loadModel('Compound');
        $this->loadModel('CompoundAttribute');
        $this->loadModel('CompoundAdhesion');
        $this->loadModel('CompoundMediaresistance');
        $this->loadModel('CompoundBurningrate');
        $this->loadModel('CompoundHotairageing');
        $this->loadModel('CompoundWeathering');
        $this->loadModel('CompoundCertification');
        $this->loadModel('CompoundConformity');
        $this->loadModel('CompoundRemark');
        $this->loadModel('CompoundSeries');
        $this->loadModel('CompoundProcessingmethod');
        $this->loadModel('CompoundDocument');

        $this->loadModel('Document');
        $this->loadModel('DocumentFile');
        $this->loadModel('Safetydata');
        $this->loadModel('SafetydataFile');
        $this->loadModel('Msds');
        $this->loadModel('MsdsFile');

        $this->loadModel('Processingset');
        $this->loadModel('ProcessingsetFile');
        
        

       //include 'modules/tpepdb/vendor/import/import.php';
       //include 'modules/tpepdb/vendor/import/import_2.php';
       include 'getpostgres/files/import.php';
       include 'getpostgres/files/import_2.php';

        
       
        // predefined data
        $this->loadModel('Files_File');

        // Rel docs
        $rel_docs_new = $this->Files_File->fetch(
            'list',
            array(
                'fields' => array(
                    'File.name',
                    'File.id'
                ),
                'conditions' => array(
                    'File.folder_id' => 52
                )
            )
        );
        
        //echo 'here';

        $q = $db->query(
            "
            SELECT d.name,
                   d.mainid
            FROM pdb2__compounds__reldocuments_main AS m
            INNER JOIN pdb2__compounds__reldocuments_data AS d
            ON m.mainid=d.mainid
            WHERE m.category = '1152633427_25865719'
            ORDER BY m.reihe
            "
        );
        $results = $q->fetchAll();
        $rel_docs = array();
        $text = new Ncw_Helpers_Text();
        foreach ($results as $result) {
            $this->Document->create();
            $this->Document->data(
                array(
                    'name' => $result['name']
                )
            );
            //$this->Document->save(false);
            // set an id because row save is out
            $this->Document->setId(28122011);
            
            $q = $db->query(
                '
                SELECT reldoc_title,
                    reldoc_file,
                    reldoc_lang
                FROM pdb2__compounds__reldocuments_reldoc
                WHERE mainid = \'' . $result['mainid'] . '\'
                '
            );
            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $file_name = explode('.', $child_result['reldoc_file']);
                array_pop($file_name);
                $file_name = implode('.', $file_name);
                $file_name = $text->cleanForUrl($file_name);
                $file_id = $rel_docs_new[$file_name];
                if ($file_id != null) {
                    $rel_docs[$result['mainid']][$child_result['reldoc_lang']] = $this->Document->getId();
                    $this->DocumentFile->create();
                    $this->DocumentFile->data(
                        array(
                            'document_id' => $this->Document->getId(),
                            'name' => $child_result['reldoc_title'],
                            'file_id' => $file_id,
                            'language' => $child_result['reldoc_lang'],
                        )
                    );
                    //$this->DocumentFile->save(false);
                }
            }
        }

        // Safety
        /*
        $q = $db->query(
            '
            SELECT d.name,
                   d.mainid
            FROM pdb2__compounds__reldocuments_main AS m
            INNER JOIN pdb2__compounds__reldocuments_data AS d
            ON m.mainid=d.mainid
            WHERE m.category = \'1152633431_19742098\'
            ORDER BY m.reihe
            '
        );
        $results = $q->fetchAll();
        $safety_data = array();
        foreach ($results as $result) {
            $this->Safetydata->create();
            $this->Safetydata->data(
                array(
                    'name' => $result['name']
                )
            );
            $this->Safetydata->save(false);

            $q = $db->query(
                '
                SELECT reldoc_title,
                    reldoc_file,
                    reldoc_lang
                FROM pdb2__compounds__reldocuments_reldoc
                WHERE mainid = \'' . $result['mainid'] . '\'
                '
            );
            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $file_name = explode('.', $child_result['reldoc_file']);
                array_pop($file_name);
                $file_name = implode('.', $file_name);
                $file_name = $text->cleanForUrl($file_name);
                $file_id = $rel_docs_new[$file_name];
                $safety_data[$result['mainid']] = $this->Safetydata->getId();
                if ($file_id != null) {
                    $this->SafetydataFile->create();
                    $this->SafetydataFile->data(
                        array(
                            'safetydata_id' => $this->Safetydata->getId(),
                            'name' => $child_result['reldoc_title'],
                            'file_id' => $file_id,
                            'language' => $child_result['reldoc_lang'],
                        )
                    );
                    $this->SafetydataFile->save(false);
                }
            }
        }*/

        // Msds
        $q = $db->query(
            "
            SELECT d.name,
                   d.mainid
            FROM pdb2__compounds__reldocuments_main AS m
            INNER JOIN pdb2__compounds__reldocuments_data AS d
            ON m.mainid=d.mainid
            WHERE m.category = '1158215088_54710427'
            ORDER BY m.reihe
            "
        );
        $results = $q->fetchAll();
        $msds_data = array();
        foreach ($results as $result) {
            $this->Msds->create();
            $this->Msds->data(
                array(
                    'name' => $result['name']
                )
            );
            $this->Msds->save(false);

            $q = $db->query(
                '
                SELECT reldoc_title,
                    reldoc_file,
                    reldoc_lang
                FROM pdb2__compounds__reldocuments_reldoc
                WHERE mainid = \'' . $result['mainid'] . '\'
                '
            );
            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $file_name = explode('.', $child_result['reldoc_file']);
                array_pop($file_name);
                $file_name = implode('.', $file_name);
                $file_name = $text->cleanForUrl($file_name);
                $file_id = $rel_docs_new[$file_name];
                $msds_data[$result['mainid']] = $this->Msds->getId();
                if ($file_id != null) {
                    $this->MsdsFile->create();
                    $this->MsdsFile->data(
                        array(
                            'msds_id' => $this->Msds->getId(),
                            'name' => $child_result['reldoc_title'],
                            'file_id' => $file_id,
                            'language' => $child_result['reldoc_lang'],
                        )
                    );
                    $this->MsdsFile->save(false);
                }
            }
        }

        // Processing information
        $vh_files_objs = $this->Files_File->fetch(
            'all',
            array(
                'fields' => array(
                    'File.id',
                    'File.name'
                ),
                'conditions' => array(
                    'File.folder_id' => 49
                )
            )
        );
        $vh_files = array();
        foreach ($vh_files_objs as $vh_file) {
            $vh_file_name = explode('_', $vh_file->getName());
            $vh_files[$vh_file_name[1] . '' . $vh_file_name[2]][$vh_file_name[4]] = $vh_file->getId();
        }
        unset($vh_files_objs);

        $q = $db->query(
            '
            SELECT d.name,
                   d.mainid,
                   m.category
            FROM pdb2__compounds__vhelements_main AS m
            INNER JOIN pdb2__compounds__vhelements_data AS d
            ON m.mainid=d.mainid
            ORDER BY m.category, d.vhnr
            '
        );
        $results = $q->fetchAll();
        $processingset_data = array();
        foreach ($results as $result) {
            $this->Processingset->create();

            if ($result['category'] == '1142197777_11560441') {
                $category_id = 1;
            } else {
                $category_id = 2;
            }
            $result['name'] = trim($result['name']);
            $result['name'] = str_replace(' Nr. ', 'Nr.', $result['name']);
            $result['name'] = str_replace(' Nr.', 'Nr.', $result['name']);
            $result['name'] = str_replace('Nr.', ' Nr. ', $result['name']);
            $this->Processingset->data(
                array(
                    'name' => $result['name'],
                    'category_id' => $category_id,
                )
            );
            $this->Processingset->save(false);
            $vh_name = trim($result['name']);
            $vh_name = str_replace(' Nr. ', 'Nr.', $vh_name);
            $vh_name = str_replace(' Nr.', 'Nr.', $vh_name);
            $vh_name = str_replace('Nr.', ' Nr. ', $vh_name);
            $vh_name = explode(' ', $vh_name);
            $vh_name = trim($vh_name[0] . $vh_name[2]);

            foreach ($vh_files[$vh_name] as $vh_file_language => $vh_file) {
                $processingset_data[trim($result['mainid'])] = $this->Processingset->getId();
                if ($vh_file != null) {
                    $this->ProcessingsetFile->create();
                    $this->ProcessingsetFile->data(
                        array(
                            'processingset_id' => $this->Processingset->getId(),
                            'file_id' => $vh_file,
                            'language' => $vh_file_language,
                        )
                    );
                    //Auskommentiert am 29.03.2012
                    $this->ProcessingsetFile->save(false);
                }
            }
        }

        $msds = array(
            '1188455044_61315489' => 1,
            '1188455044_61299364' => 2,
            '1188455044_61267024' => 3,
            '1188455044_61251298' => 4,
            '1188455044_61234824' => 5,
            '1188455044_61217463' => 6,
        );
        
        
        $environments = array(
            '1141670003_51100546' => 1,
            '1141670003_51113726' => 2,
            '1141670003_51109598' => 3,
        );

        $status = array(
            '1141747989_71701951' => 'Prototype',
            '1141747989_77573078' => 'First Sampling',
            '1141747989_77652358' => 'Series',
        );

        $colors = array(
            '1141720253_90546344' => 1,
            '1141720253_94373281' => 2,
            '1141720253_94454885' => 3,
            '1141720253_94541694' => 4,
            '1141720253_94625127' => 5,
            '1141720253_94708165' => 6,
            '1141720253_94789394' => 7,
        );

        $header_datasheets = array(
            '1154930900_95035019' => 'Data sheet',
            '1154930910_25446776' => 'Test report',
        );

        $visible = array(
            't' => 1,
            'f' => 0,
        );

        $tpe_types = array(
            '1176285818_74619118' => 1,
            '1218436699_36922237' => 2,
            '1218436713_38968524' => 3,
            '1222095361_37229831' => 4,
            '1245237304_87553475' => 5,
            '1251360066_61840162' => 6,
            '1251360114_03152798' => 7,
            '1251360151_76293316' => 8,
            '1251360177_36289087' => 9,
            '1251360197_57319861' => 10,
        );

        $adhesion_quality = array(
            '1141669281_38784177' => 'Adhesion',
            '1141669281_38792958' => 'Cohesion',
        );

        $mediaresistance_general = array(
            '1141713452_25988755' => 'excellent',
            '1141713452_36478345' => 'fair',
            '1141713452_36301276' => 'good',
            '1153064030_06887087' => 'not resistant',
        );

        $mediaresistance_medium = array(
            '1141715505_20297279' => 1,
            '1141715505_10358866' => 2,
            '1153064482_24239658' => 3,
            '1178197867_19175784' => 4,
            '1141715505_20972224' => 5,
            '1178197898_44613622' => 6,
            '1141715505_21052424' => 7,
            '1178197913_76859556' => 8,
            '1141715505_21135924' => 9,
            '1178197683_25262545' => 10,
            '1178197700_81365776' => 11,
            '1141715505_20216046' => 12,
            '1178196568_72580186' => 13,
            '1141715505_20806117' => 14,
            '1178197669_50545491' => 15,
            '1153064467_90695277' => 16,
            '1141715505_20889293' => 17,
            '1178197837_06467226' => 18,
            '1141715505_21545293' => 19,
            '1178198046_42265379' => 20,
            '1178197723_33790345' => 21,
            '1222870498_78977698' => 22,
            '1141715505_20383616' => 23,
            '1141715505_20469462' => 24,
            '1222870516_49313316' => 25,
            '1141715505_20553538' => 26,
            '1141715505_21218972' => 27,
            '1141715505_21468424' => 28,
            '1178197942_12142963' => 29,
            '1178197965_57029761' => 30,
            '1178197988_70568214' => 31,
            '1178198014_50654299' => 32,
            '1141715505_20639335' => 33,
            '1141715505_20724051' => 34,
            '1141715505_21705144' => 35,
            '1141715505_21633098' => 36,
            '1178197741_48145492' => 37,
            '1178198035_05550893' => 38,
            '1153064498_51319621' => 39,
            '1141715505_21305328' => 40,
            '1141715505_21388352' => 41,
            '1178197763_98761169' => 42,
        );

        $hotairageing_general = array(
            '1141713452_36217416' => 'excellent',
            '1141713452_36565518' => 'fair',
            '1141713452_36386046' => 'good',
            '1153064166_18120378' => 'not resistant',
        );

        $certifications = array(
            '1185829599_44980744 ' => 1,
            '1240498765_53515596' => 2,
            '1185829599_46201615' => 3,
            '1185829599_45282689' => 4,
            '1185829599_45404284' => 5,
            '1185829599_44241591' => 6,
            '1185829599_45902288' => 7,
            '1185829599_44880092' => 8,
            '1185829599_44457885' => 9,
            '1185829599_44349939' => 10,
            '1185829599_45178884' => 11,
            '1185829599_45082474' => 12,
            '1185829599_44140016' => 13,
        );

        $conformities = array(
            '1179430975_50316132' => 1,
            '1180615742_22794558' => 2,
            '1180615773_62196974' => 3,
            '1180615804_55767793' => 4,
            '1180615952_91212491' => 5,
            '1180616094_12289517' => 6,
            '1180616140_23954711' => 7,
            '1180616190_21871144' => 8,
            '1180616253_12223267' => 9,
            '1180616266_53161335' => 10,
            '1186146591_83208733' => 11,
            '1192711731_59158861' => 12,
            '1220015793_83756024' => 13,
        );

        $remarks = array(
            '1141712432_15997587' => 1,
            '1141712432_20524263' => 2,
            '1141712432_20606173' => 3,
            '1141712432_20696897' => 4,
            '1153059562_45501775' => 5,
            '1153059573_76763461' => 6,
            '1153059585_84946993' => 7,
            '1153059596_65463154' => 8,
            '1153059610_11684138' => 9,
            '1153059627_42697117' => 10,
            '1153059643_84919932' => 11,
            '1153059978_76993398' => 12,
            '1153059986_96005954' => 13,
            '1153059996_79092738' => 14,
            '1153060003_63257543' => 15,
            '1153060015_63041255' => 16,
            '1153060021_72179024' => 17,
            '1222871088_26420563' => 18,
            '1238056509_25392855' => 19,
        );

        $processingmethods = array(
            '1178469825_98422058' => 1,
            '1178469842_65998163' => 2,
            '1178469833_92660269' => 3,
            '1178469810_27129183' => 4,
            '1178469794_53360189' => 5,
        );

        $this->loadModel('Brand');
        $brands = $this->Brand->fetch('list', array('fields' => array('Brand.en', 'Brand.id')));

        $this->loadModel('Standardisation');
        $standardisations_1 = $this->Standardisation->fetch(
            'list',
            array(
                'fields' => array(
                    'Standardisation.main_id_tpm', // statt.name
                    'Standardisation.id'
                )
            )
        );
        
        $q = $db->query(
            '
            SELECT standardisation.mainid,
                   standardisation.svalue
            FROM pdb2__compounds__standardisation_data AS standardisation
            '
        );
        $standardisations = array();
        $results = $q->fetchAll();
        foreach ($results as $result) {
            //$standardisations[$result['mainid']] = (int) $standardisations_1[$result['svalue']];
            $standardisations[$result['mainid']] = (int) $standardisations_1[$result['mainid']];
        }
        unset($standardisations_1);

        $this->loadModel('Condition');
        $conditions_1 = $this->Condition->fetch('list', array('fields' => array('Condition.main_id_tpm', 'Condition.id')));
        //$conditions_1 = $this->Condition->fetch('list', array('fields' => array('Condition.id', 'Condition.id')));
        $q = $db->query(
            '
            SELECT con.mainid,
                   con.name
            FROM pdb2__compounds__conditions_data AS con
            '
        );
        $conditions = array();
        $results = $q->fetchAll();
        foreach ($results as $result) {
            //$conditions[$result['mainid']] = (int) $conditions_1[str_replace('Â', '', $result['name'])];
            $conditions[$result['mainid']] = (int) $conditions_1[$result['mainid']];
        }

        // IMPORT Series
        $q = $db->query(
            '
            SELECT series.mainid,
                   series.name
            FROM pdb2__compounds__reihen_data AS series
            '
        );
        $series = array();
        $results = $q->fetchAll();
        foreach ($results as $result) {
            if ($result['name'] == 'stand-alone') {
                $result['name'] = 'special product';
            }
            $this->Series->create();
            $this->Series->data(
                array(
                    'name' => $result['name']
                )
            );
            $this->Series->save(false);

            $series[$result['mainid']] = $this->Series->getId();
        }

        // IMPORT Compounds
        $q = $db->query(
            '
            SELECT main.mainid,
                   main.name,
                   main.devname,
                   main.innen_aussen,
                   main.farbe,
                   main.visible,
                   main.ctype,
                   main.brandname,
                   main.tpe_type,
                   main.dshead,
                   main.sd,
                   main.msds,
                   main.vh_ex,
                   main.vh_sg

            FROM pdb2__compounds__ncompounds_data AS main
            '
        );

        $results = $q->fetchAll();
        $compounds = array();
        foreach ($results as $result) {
        	//echo 't';
            // Compound
            /*
            $compound_data = array(
                'name' => $result['name'],
                'development_name' => $result['devname'],
                'environment_id' => $environments[$result['innen_aussen']],
                'color_id' => $colors[$result['farbe']],
                'visible' => $visible[$result['visible']],
                'status' => $status[$result['ctype']],
                'brand_id' => $brands[$result['brandname']],
                'tpetype_id' => $tpe_types[$result['tpe_type']],
                'safetydata_id' => $safety_data[$result['sd']],
                'msds_id' => $msds_data[$result['msds']],
                'processingset_ex_id' => $processingset_data[$result['vh_ex']],
                'processingset_im_id' => $processingset_data[$result['vh_sg']],
                'header_datasheet' => $header_datasheets[$result['dshead']],
            );*/
        	
        	$safetydata_id = $this->_compound_safetydata($result['name'], $result['devname']);
            
            
            
        	$msds_id = $msds[$result['msds']];
        	//'msds_id' => $msds_data[$result['msds']],
        	//echo '<br />sd=' . $safetydata_id;
            if (trim($result['vh_sg']) == '1332141010_85820488__') {
                //$processingset_im_id = 13;
            } else if (trim($result['vh_sg']) == '1332141885_12129838__') {
                //$processingset_im_id = 14;
            } else {
                $processingset_im_id = $processingset_data[trim($result['vh_sg'])];
            }
            
            $compound_data = array(
                'name' => $result['name'],
                'development_name' => $result['devname'],
                'environment_id' => $environments[$result['innen_aussen']],
                'color_id' => $colors[$result['farbe']],
                'visible' => $visible[$result['visible']],
                'status' => $status[$result['ctype']],
                'brand_id' => $brands[$result['brandname']],
                'tpetype_id' => $tpe_types[$result['tpe_type']],
                'safetydata_id' => $safetydata_id,
                'msds_id' => $msds_id,
                'processingset_ex_id' => $processingset_data[$result['vh_ex']],
                'processingset_im_id' => $processingset_im_id,
                'header_datasheet' => $header_datasheets[$result['dshead']],
            );
            $values = array();
            foreach ($compound_data as $value) {
                $values[] = '\'' . $value . '\'';
            }
            $compounds[] = '('. implode(',', $values) . ')';

            $this->Compound->create();
            $this->Compound->data($compound_data);
            //print '<h1>Compound ' . $result['name'] . ' Save</h1>';
            $this->Compound->save(false);

            

            // Related Docs
            /*
            $q = $db->query(
               '
               SELECT
               reldocsref_reldocuments_reldocsref
               FROM
               pdb2__compounds__ncompounds_reldocsref AS c_d
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );
            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === isset($rel_docs[$child_result['reldocsref_reldocuments_reldocsref']])) {
                    foreach ($rel_docs[$child_result['reldocsref_reldocuments_reldocsref']] as $doc) {
                        $this->CompoundDocument->create();
                        $this->CompoundDocument->data(
                            array(
                                'compound_id' => $this->Compound->getId(),
                                'document_id' => $doc,
                            )
                        );
                        $this->CompoundDocument->save(false);
                    }
                }
            }*/

            // Hardness
            $q = $db->query(
               '
               SELECT
               haerte_haerte,
               haerte_standardisation_haerte
               FROM
               pdb2__compounds__ncompounds_haerte
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            // 1141643139_82865641 = Shore A
            // 1141643182_81426772 = Shore D
            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {

                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 1,
                        'condition_id' => 0,
                        'standardisation_id' => $standardisations[$child_result['haerte_standardisation_haerte']],
                        'value_1' => (float) $child_result['haerte_haerte'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Density
            $q = $db->query(
               '
               SELECT
               dichte_wert,
               dichte_standardisation_dichte
               FROM
               pdb2__compounds__ncompounds_dichte
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 2,
                        'condition_id' => 0,
                        'standardisation_id' => $standardisations[$child_result['dichte_standardisation_dichte']],
                        'value_1' => (float) $child_result['dichte_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Shrinkage
            $q = $db->query(
               '
               SELECT
               schwindung_wert,
               schwindung_wert2,
               schwindung_conditions_schwindung
               FROM
               pdb2__compounds__ncompounds_schwindung
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 3,
                        'condition_id' => $conditions[$child_result['schwindung_conditions_schwindung']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['schwindung_wert'],
                        'value_2' => (float) $child_result['schwindung_wert2'],
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }
            
            // Compression set
            $q = $db->query(
               '
               SELECT
               csets_wert,
               csets_relation,
               csets_conditions_csets
               FROM
               pdb2__compounds__ncompounds_csets
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 4,
                        'condition_id' => $conditions[$child_result['csets_conditions_csets']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['csets_wert'],
                        'value_2' => 0,
                        'relation' => $child_result['csets_relation'],
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Abrasion
            $q = $db->query(
               '
               SELECT
               abrieb_wert,
               abrieb_standardisation_abrieb
               FROM
               pdb2__compounds__ncompounds_abrieb
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 5,
                        'condition_id' => 0,
                        'standardisation_id' => $standardisations[$child_result['abrieb_standardisation_abrieb']],
                        'value_1' => (float) $child_result['abrieb_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Transmission
            $q = $db->query(
               '
               SELECT
               transparency_wert,
               transparency_standardisation_transparency
               FROM
               pdb2__compounds__ncompounds_transparency
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 6,
                        'condition_id' => 0,
                        'standardisation_id' => $standardisations[$child_result['transparency_standardisation_transparency']],
                        'value_1' => (float) $child_result['transparency_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Haze
            $q = $db->query(
               '
               SELECT
               haze_wert,
               haze_standardisation_haze
               FROM
               pdb2__compounds__ncompounds_haze
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 7,
                        'condition_id' => 0,
                        'standardisation_id' => $standardisations[$child_result['haze_standardisation_haze']],
                        'value_1' => (float) $child_result['haze_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Clarety
            $q = $db->query(
               '
               SELECT
               clearity_wert,
               clearity_standardisation_clearity
               FROM
               pdb2__compounds__ncompounds_clearity
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 8,
                        'condition_id' => 0,
                        'standardisation_id' => $standardisations[$child_result['clearity_standardisation_clearity']],
                        'value_1' => (float) $child_result['clearity_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Tensile Strengh
            $q = $db->query(
               '
               SELECT
               reissfestigkeit_wert,
               reissfestigkeit_conditions_reissfestigkeit
               FROM
               pdb2__compounds__ncompounds_reissfestigkeit
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 9,
                        'condition_id' => $conditions[$child_result['reissfestigkeit_conditions_reissfestigkeit']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['reissfestigkeit_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Tear resistance
            $q = $db->query(
               '
               SELECT
               wreissfestigkeit_wert,
               wreissfestigkeit_standardisation_wreissfestigkeit
               FROM
               pdb2__compounds__ncompounds_wreissfestigkeit
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 10,
                        'condition_id' => 0,
                        'standardisation_id' => $standardisations[$child_result['wreissfestigkeit_standardisation_wreissfestigkeit']],
                        'value_1' => (float) $child_result['wreissfestigkeit_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Elongation at break
            $q = $db->query(
               '
               SELECT
               reissdehnung_wert,
               reissdehnung_conditions_reissdehnung
               FROM
               pdb2__compounds__ncompounds_reissdehnung
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 11,
                        'condition_id' => $conditions[$child_result['reissdehnung_conditions_reissdehnung']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['reissdehnung_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Rest elongation
            /*
            $q = $db->query(
               '
               SELECT
               restelongation_wert,
               restelongation_conditions_restelongation
               FROM
               pdb2__compounds__ncompounds_restelongation
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 12,
                        'condition_id' => $conditions[$child_result['restelongation_conditions_restelongation']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['restelongation_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                //$this->CompoundAttribute->save(false);
            }*/

            // Module
            $q = $db->query(
               '
               SELECT modul_wert,
               modul_conditions_modul
               FROM pdb2__compounds__ncompounds_modul
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 13,
                        'condition_id' => $conditions[$child_result['modul_conditions_modul']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['modul_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Melting temperature
            /*
            $q = $db->query(
               '
               SELECT
               melttemp_wert,
               melttemp_conditions_melttemp
               FROM
               pdb2__compounds__ncompounds_melttemp
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 14,
                        'condition_id' => $conditions[$child_result['melttemp_conditions_melttemp']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['melttemp_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                //$this->CompoundAttribute->save(false);
            }*/

            // Spiral Flow
            $q = $db->query(
               '
               SELECT
               fspirale_wert,
               fspirale_conditions_fspirale
               FROM
               pdb2__compounds__ncompounds_fspirale
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 17,
                        'condition_id' => $conditions[$child_result['fspirale_conditions_fspirale']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['fspirale_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // MFI
            $q = $db->query(
               '
               SELECT
               mfi_wert,
               mfi_conditions_mfi,
               mfi_relation
               FROM
               pdb2__compounds__ncompounds_mfi
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 18,
                        'condition_id' => $conditions[$child_result['mfi_conditions_mfi']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['mfi_wert'],
                        'value_2' => 0,
                        'relation' => $result['mfi_relation'],
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Relative permittivity
            /*
            $q = $db->query(
               '
               SELECT
               dielkonst_wert,
               dielkonst_standardisation_dielkonst
               FROM
               pdb2__compounds__ncompounds_dielkonst
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 19,
                        'condition_id' => 0,
                        'standardisation_id' => $standardisations[$child_result['dielkonst_standardisation_dielkonst']],
                        'value_1' => (float) $child_result['dielkonst_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }*/

            // Dissipation factor
            /*
            $q = $db->query(
               '
               SELECT
               dielverlustf_wert,
               dielverlustf_standardisation_dielverlustf
               FROM
               pdb2__compounds__ncompounds_dielverlustf
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 20,
                        'condition_id' => 0,
                        'standardisation_id' => $standardisations[$child_result['dielverlustf_standardisation_dielverlustf']],
                        'value_1' => (float) $child_result['dielverlustf_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }*/

            // Volume resistivity
            /*
            $q = $db->query(
               '
               SELECT
               dwiderstand_wert,
               dwiderstand_conditions_dwiderstand,
               dwiderstand_relation
               FROM
               pdb2__compounds__ncompounds_dwiderstand
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 22,
                        'condition_id' => $conditions[$child_result['dwiderstand_conditions_dwiderstand']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['dwiderstand_wert'],
                        'value_2' => 0,
                        'relation' => $child_result['dwiderstand_relation'],
                    )
                );
                $this->CompoundAttribute->save(false);
            }*/

            // Surface resistance
            /*
            $q = $db->query(
               '
               SELECT
               surface_wert,
               surface_conditions_surface,
               surface_relation
               FROM
               pdb2__compounds__ncompounds_surface
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 23,
                        'condition_id' => $conditions[$child_result['surface_c$child_resultns_surface']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['surface_wert'],
                        'value_2' => 0,
                        'relation' => $child_result['surface_relation'],
                    )
                );
                $this->CompoundAttribute->save(false);
            }*/

            // Fogging
            $q = $db->query(
               '
               SELECT
               fogging_wert,
               fogging_conditions_fogging
               FROM
               pdb2__compounds__ncompounds_fogging
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 25,
                        'condition_id' => $conditions[$child_result['fogging_conditions_fogging']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['fogging_wert'],
                        'value_2' => 0,
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Odour
            $q = $db->query(
               '
               SELECT
               geruch_anf,
               geruch_conditions_geruch,
               geruch_note
               FROM
               pdb2__compounds__ncompounds_geruch
               WHERE
               mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                $this->CompoundAttribute->create();
                $this->CompoundAttribute->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'attribute_id' => 26,
                        'condition_id' => $conditions[$child_result['geruch_conditions_geruch']],
                        'standardisation_id' => 0,
                        'value_1' => (float) $child_result['geruch_anf'],
                        'value_2' => (float) $child_result['geruch_note'],
                        'relation' => '',
                    )
                );
                $this->CompoundAttribute->save(false);
            }

            // Adhesion
            $q = $db->query(
               '
               SELECT zweikmit_wert,
               zweikmit_rhaftwert,
               zweikmit_standardisation_zweikmit,
               zweikmit_hqual
               FROM pdb2__compounds__ncompounds_zweikmit
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === empty($child_result['zweikmit_standardisation_zweikmit'])) {
                    continue;
                }
                $this->CompoundAdhesion->create();
                $this->CompoundAdhesion->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'type' => $child_result['zweikmit_wert'],
                        'value' => (float) $child_result['zweikmit_rhaftwert'],
                        'quality' => $adhesion_quality[$child_result['zweikmit_hqual']],
                        'standardisation_id' => $standardisations[$child_result['zweikmit_standardisation_zweikmit']],
                    )
                );
                //print '<h2>Adhesion Save</h2>';
                $this->CompoundAdhesion->save(false);
            }

            // Medium resistance
            $q = $db->query(
               '
               SELECT bestmedium_conditions_bestmedium,
               bestmedium_medium,
               bestmedium_haerte,
               bestmedium_density,
               bestmedium_volume,
               bestmedium_gewicht,
               bestmedium_zfestigkeit,
               bestmedium_bruchdehnung,
               bestmedium_aussage,
               bestmedium_module
               FROM pdb2__compounds__ncompounds_bestmedium
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === empty($child_result['bestmedium_conditions_bestmedium'])) {
                    continue;
                }
                $this->CompoundMediaresistance->create();
                $this->CompoundMediaresistance->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'medium_id' => $mediaresistance_medium[$child_result['bestmedium_medium']],
                        'tear_resistance' => (float) $child_result['bestmedium_bruchdehnung'],
                        'elongation_at_break' => (float) $child_result['bestmedium_zfestigkeit'],
                        'hardness' => (float) $child_result['bestmedium_haerte'],
                        'density' => (float) $child_result['bestmedium_density'],
                        'weight' => (float) $child_result['bestmedium_gewicht'],
                        'volume' => (float) $child_result['bestmedium_volume'],
                        'module' => (float) $child_result['bestmedium_module'],
                        'general' => $mediaresistance_general[$child_result['bestmedium_aussage']],
                        'condition_id' => $conditions[$child_result['bestmedium_conditions_bestmedium']],
                    )
                );
                //print '<h2>Mediaresistance Save</h2>';
                $this->CompoundMediaresistance->save(false);
            }

            // Hot air ageing
            $q = $db->query(
               '
               SELECT halterung_conditions_halterung,
               halterung_haerte,
               halterung_density,
               halterung_volume,
               halterung_gewicht,
               halterung_zfestigkeit,
               halterung_bruchdehnung,
               halterung_aussage
               FROM pdb2__compounds__ncompounds_halterung
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === empty($child_result['halterung_conditions_halterung'])) {
                    continue;
                }
                $this->CompoundHotairageing->create();
                $this->CompoundHotairageing->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'tear_resistance' => (float) $child_result['halterung_bruchdehnung'],
                        'elongation_at_break' => (float) $child_result['halterung_zfestigkeit'],
                        'hardness' => (float) $child_result['halterung_haerte'],
                        'density' => (float) $child_result['halterung_density'],
                        'weight' => (float) $child_result['halterung_gewicht'],
                        'volume' => (float) $child_result['halterung_volume'],
                        'general' => $hotairageing_general[$child_result['halterung_aussage']],
                        'condition_id' => $conditions[$child_result['halterung_conditions_halterung']],
                    )
                );
                //print '<h2>Hot air ageing Save</h2>';
                $this->CompoundHotairageing->save(false);
            }

            // Weathering
            $q = $db->query(
               '
               SELECT bewitterung_conditions_bewitterung,
               bewitterung_e,
               bewitterung_graum,
               bewitterung_zfestigkeit,
               bewitterung_bruchdehnung
               FROM pdb2__compounds__ncompounds_bewitterung
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === empty($child_result['bewitterung_conditions_bewitterung'])) {
                    continue;
                }
                $this->CompoundWeathering->create();
                $this->CompoundWeathering->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'tear_resistance' => (float) $child_result['bewitterung_bruchdehnung'],
                        'elongation_at_break' => (float) $child_result['bewitterung_zfestigkeit'],
                        'delta_e' => (float) $child_result['bewitterung_e'],
                        'greyscale' => (float) $child_result['bewitterung_graum'],
                        'condition_id' => $conditions[$child_result['bewitterung_conditions_bewitterung']],
                    )
                );
                //print '<h2>Weathering Save</h2>';
                $this->CompoundWeathering->save(false);
            }

            // Burning rate
            $q = $db->query(
               '
               SELECT uvbest_wert,
               uvbest_conditions_uvbest,
               uvbest_anf
               FROM pdb2__compounds__ncompounds_uvbest
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === empty($child_result['uvbest_conditions_uvbest'])) {
                    continue;
                }
                $this->CompoundBurningrate->create();
                $this->CompoundBurningrate->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'value' => (float) $child_result['uvbest_wert'],
                        'requirements' => (float) $child_result['uvbest_anf'],
                        'condition_id' => $conditions[$child_result['uvbest_conditions_uvbest']],
                    )
                );
                //print '<h2>Burning rate Save</h2>';
                $this->CompoundBurningrate->save(false);
            }

            // Certification
            $q = $db->query(
               '
               SELECT zulassungen_zul
               FROM pdb2__compounds__ncompounds_zulassungen
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === empty($child_result['zulassungen_zul'])) {
                    continue;
                }
                $this->CompoundCertification->create();
                $this->CompoundCertification->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'certification_id' => $certifications[$child_result['zulassungen_zul']],
                    )
                );
                //print '<h2>Certification Save</h2>';
                $this->CompoundCertification->save(false);
            }

            // Conformity
            $q = $db->query(
               '
               SELECT conformities_conf
               FROM pdb2__compounds__ncompounds_conformities
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === empty($child_result['conformities_conf'])) {
                    continue;
                }
                $this->CompoundConformity->create();
                $this->CompoundConformity->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'conformity_id' => $conformities[$child_result['conformities_conf']],
                    )
                );
                //print '<h2>Conformity Save</h2>';
                $this->CompoundConformity->save(false);
            }

            // Remark
            $q = $db->query(
               '
               SELECT bemerkungen_bem
               FROM pdb2__compounds__ncompounds_bemerkungen
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === empty($child_result['bemerkungen_bem'])) {
                    continue;
                }
                $this->CompoundRemark->create();
                $this->CompoundRemark->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'remark_id' => $remarks[$child_result['bemerkungen_bem']],
                    )
                );
                //print '<h2>Remark Save</h2>';
                $this->CompoundRemark->save(false);
            }

            // Series
            $q = $db->query(
               '
               SELECT subreihen_reihen
               FROM pdb2__compounds__ncompounds_subreihen
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === empty($child_result['subreihen_reihen'])) {
                    continue;
                }
                $this->CompoundSeries->create();
                $this->CompoundSeries->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'series_id' => $series[$child_result['subreihen_reihen']],
                    )
                );
                //print '<h2>Series Save</h2>';
                $this->CompoundSeries->save(false);
            }

            // Processing method
            $q = $db->query(
               '
               SELECT pmethod_pmethod
               FROM pdb2__compounds__ncompounds_pmethod
               WHERE mainid=\'' . $result['mainid'] . '\''
            );

            $child_results = $q->fetchAll();
            foreach ($child_results as $child_result) {
                if (true === empty($child_result['pmethod_pmethod'])) {
                    continue;
                }
                $this->CompoundProcessingmethod->create();
                $this->CompoundProcessingmethod->data(
                    array(
                        'compound_id' => $this->Compound->getId(),
                        'processingmethod_id' => $processingmethods[$child_result['pmethod_pmethod']],
                    )
                );
                //print '<h2>Processing method Save</h2>';
                $this->CompoundProcessingmethod->save(false);
            }
            
            

        }
        
        // Import "freitext"
        $delete_query = "DELETE FROM pdb2__data__langelements WHERE field != 'freitext'";
        $q = $db->query(
            $delete_query
        );
        $read_query = "
            SELECT 
                elements.value ,
                elements.language ,
                compounds.devname AS devname
            FROM pdb2__data__langelements AS elements 
            INNER JOIN pdb2__compounds__ncompounds_data AS compounds
            ON compounds.mainid = elements.mainid
            ORDER by elements.mainid 
            ";
        $q = $db->query(
            $read_query
        );
        $child_results = $q->fetchAll();
        $arr_debug = array();
        foreach ($child_results as $child_result) {
            $obj_compound = new Tpepdb_Compound();
            $compound = $obj_compound->fetch(
                'id',
                array(
                    'conditions' => array(
                        'development_name' => $child_result['devname']
                    )
                )
            );
            $obj_compound->setId($compound[0]->getId());
            if ($obj_compound->getName() != 'TM6LFT') {
                if ($child_result['language'] == 'en') {
                    $obj_compound->setInfoEn($child_result['value']);
                    $obj_compound->saveField('info_en');
                } else if ($child_result['language'] == 'de') {
                    $obj_compound->setInfoDe($child_result['value']);
                    $obj_compound->saveField('info_de');
                } else if ($child_result['language'] == 'fr') {
                    $obj_compound->setInfoFr($child_result['value']);
                    $obj_compound->saveField('info_fr');
                } else if ($child_result['language'] == 'es') {
                    $obj_compound->setInfoEs($child_result['value']);
                    $obj_compound->saveField('info_es');
                } else if ($child_result['language'] == 'it') {
                    $obj_compound->setInfoIt($child_result['value']);
                    $obj_compound->saveField('info_it');
                } else if ($child_result['language'] == 'cn') {
                    $obj_compound->setInfoCn($child_result['value']);
                    $obj_compound->saveField('info_cn');
                }
            }
        }
        

        ob_get_clean();
        
        echo 'Import Done';
    }
    
    /**
     * decides which safetydata fits to an compound
     * @param $name
     * @param $development_name
     * @return int safetydata id
     */
    private function _compound_safetydata($name, $development_name)
    {        
        if (true == strstr($name, 'HX') 
           || true == strstr($development_name, 'HX')
           || true == strstr($name, 'HTX')
           || true == strstr($development_name, 'HTX')
        ) {
            $safetydata_id = 8;
        } else if (true == strstr($name, 'FOAM')) {    
            $safetydata_id = 7;
        } else if (true == strstr($name, 'HTF8675/69')) {    
            $safetydata_id = 16;
        } else if (true == strstr($name, 'HTF8675/77') || true == strstr($name , 'ODFPEP001')) {    
            $safetydata_id = 17;
        } else if (true == strstr($name, 'HTF8675/79')) {    
            $safetydata_id = 14;
        } else if (true == strstr($name, 'HTF8675/80')) {    
            $safetydata_id = 15;
        } else if ('TM' == substr($name, 0, 2) || 'HTM' == substr($name, 0, 3)) {    
            $safetydata_id = 18;
	    } else if ('OC' == substr($name, 0, 2) || 'SOC' == substr($name, 0, 3) || 'HOC' == substr($name, 0, 3)) {
	       $safetydata_id = 20; // ehemals 19 also die alte copec
        } else if ('O' == substr($name, 0, 1) || 'O' == substr($development_name, 1, 1)) {
           $safetydata_id = 20; // For-Tec E
        } else if (('C' == substr($name, 0, 1) || 'C' == substr($development_name, 1, 1)) && 'TC' != substr($development_name, 0, 2)) {
           $safetydata_id = 21; // COPEC®
        } else {
            $safetydata_id = 3;
        }
        return $safetydata_id;
    }
    
    /**
     * get Id of msds in new database
     * @param $msds_id
     * @return unknown_type
     */
    private function _compound_msdsdata($msds_id)
    {
    	if ($msds_id == '1188455044_61315489') {
    		
    	}
    	return $msds_id;
    }
    
}
?>
