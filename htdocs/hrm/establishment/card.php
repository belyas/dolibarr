<?php
/* Copyright (C) 2015      Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       	htdocs/hrm/establishment/card.php
 *  \brief      	Page to show an establishment
 */
require('../../main.inc.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/hrm.lib.php';
require_once DOL_DOCUMENT_ROOT.'/hrm/class/establishment.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';

$langs->load("admin");
$langs->load("hrm");

// Security check
if (! $user->admin) accessforbidden();

$error=0;

$action = GETPOST('action','alpha');
$cancel = GETPOST('cancel', 'alpha');
$confirm = GETPOST('confirm','alpha');
$id = GETPOST('id','int');

// List of status
static $tmpstatus2label=array(
		'0'=>'OpenEtablishment',
		'1'=>'CloseEtablishment'
);
$status2label=array('');
foreach ($tmpstatus2label as $key => $val) $status2label[$key]=$langs->trans($val);

$object = new Establishment($db);

/*
 * Actions
 */

if ($action == 'confirm_delete' && $confirm == "yes")
{
    $result=$object->delete($id);
    if ($result >= 0)
    {
        header("Location: ../admin/admin_establishment.php");
        exit;
    }
    else
    {
        setEventMessage($object->error, 'errors');
    }
}

else if ($action == 'add')
{
    if (! $cancel)
    {
        $error=0;

		$object->name = GETPOST('name', 'alpha');
        if (empty($object->name))
        {
	        setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Name")), 'errors');
            $error++;
        }

        if (empty($error))
        {
			$object->address 		= GETPOST('address', 'alpha');
			$object->zip 			= GETPOST('zipcode', 'alpha');
			$object->town			= GETPOST('town', 'alpha');
			$object->country_id     = $_POST["country_id"];
			$object->status     	= GETPOST('status','int');
			$object->fk_user_author	= $user->id;
			$object->datec			= dol_now();

			

			$id = $object->create($user);

            if ($id > 0)
            {
                header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $id);
                exit;
			}
			else
			{
				setEventMessages($object->error, $object->errors, 'errors');
			}
        }
        else
        {
            $action='create';
        }
    }
    else
    {
        header("Location: ../admin/admin_establishment.php");
        exit;
    }
}

// Update record
else if ($action == 'update')
{
	$error = 0;
	
	if (! $cancel) {

		$name = GETPOST('name', 'alpha');
		if (empty($name)) {
			setEventMessage($langs->trans('ErrorFieldRequired', $langs->trans('Name')), 'errors');
			$error ++;
		}

		if (empty($error)) {
			$object->name 			= GETPOST('name', 'alpha');
			$object->address 		= GETPOST('address', 'alpha');
			$object->zip 			= GETPOST('zipcode', 'alpha');
			$object->town			= GETPOST('town', 'alpha');
			$object->country_id     = $_POST["country_id"];
			$object->fk_user_mod	= $user->id;

			$result = $object->update();

            if ($result > 0)
            {
                header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $_POST['id']);
                exit;
            }
			else
			{
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}
	} else {
        header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $_POST['id']);
        exit;
	}
}

/*
 * View
 */

llxHeader();

$form = new Form($db);
$formcompany = new FormCompany($db);

/*
 * Action create
 */
if ($action == 'create')
{
    print load_fiche_titre($langs->trans("NewEstablishment"));

    print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="add">';

	dol_fiche_head();

    print '<table class="border" width="100%">';

	// Name
    print '<tr><td>'. fieldLabel('Name','name',1).'</td><td><input name="name" id="name" size="32" value="' . GETPOST("name") . '"></td></tr>';

	// Address
	print '<tr>';
	print '<td>'.fieldLabel('Address','address',0).'</td>';
	print '<td>';
	print '<input name="address" id="address" size="32" value="' . $object->address . '">';
	print '</td>';
	print '</tr>';

	// Zipcode
	print '<tr>';
	print '<td>'.fieldLabel('Zip','zipcode',0).'</td>';
	print '<td>';
	print $formcompany->select_ziptown(GETPOST('zipcode', 'alpha'), 'zipcode', array (
			'town',
			'selectcountry_id' 
	), 6);
	print '</td>';
	print '</tr>';
	
	// Town
	print '<tr>';
	print '<td>'.fieldLabel('Town','town',0).'</td>';
	print '<td>';
	print $formcompany->select_ziptown(GETPOST('town', 'alpha'), 'town', array (
			'zipcode',
			'selectcountry_id' 
	));
	print '</td>';
	print '</tr>';

	// Country
	print '<tr>';
	print '<td>'.fieldLabel('Country','selectcountry_id',0).'</td>';
	print '<td class="maxwidthonsmartphone">';
	print $form->select_country($mysoc->country_id,'country_id');
		if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
	print '</td>';
	print '</tr>';

	// Status
    print '<tr>';
    print '<td>'.fieldLabel('Status','status',1).'</td>';
	print '<td>';
	print $form->selectarray('status',$status2label,GETPOST('status'));
    print '</td></tr>';

    print '</table>';
	
	dol_fiche_end();

    print '<div class="center">';
	print '<input class="button" type="submit" value="'.$langs->trans("Save").'">';
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    print '<input class="button" type="submit" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

    print '</form>';
}
else if ($id)
{
    $result = $object->fetch($id);
    if ($result > 0)
    {
        $head = establishment_prepare_head($object);

        if ($action == 'edit')
        {
        	dol_fiche_head($head, 'card', $langs->trans("Establishment"), 0, 'building');

        	print '<form name="update" action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            print '<input type="hidden" name="action" value="update">';
            print '<input type="hidden" name="id" value="'.$id.'">';

            print '<table class="border" width="100%">';

            // Ref
            print "<tr>";
            print '<td width="25%">'.$langs->trans("Ref").'</td><td>';
            print $object->id;
            print '</td></tr>';

            // Name
            print '<tr><td>'.fieldLabel('Name','name',1).'</td><td>';
            print '<input name="name" id="name" class="flat" size="32" value="'.$object->name.'">';
            print '</td></tr>';

			// Address
			print '<tr><td>'.fieldLabel('Address','address',0).'</td>';
			print '<td>';
			print '<input name="address" id="address" size="32" value="' . $object->address . '">';
			print '</td></tr>';

			// Zipcode / Town
			print '<tr><td>'.fieldLabel('Zip','zipcode',0).'</td><td>';
			print $formcompany->select_ziptown($object->zip, 'zipcode', array (
					'town',
					'selectcountry_id' 
			), 6) . '</tr>';
			print '<tr><td>'.fieldLabel('Town','town',0).'</td><td>';
			print $formcompany->select_ziptown($object->town, 'town', array (
					'zipcode',
					'selectcountry_id' 
			)) . '</td></tr>';

			// Country
			print '<tr><td>'.fieldLabel('Country','selectcountry_id',0).'</td>';
			print '<td class="maxwidthonsmartphone">';
			print $form->select_country($object->fk_country,'country_id');
				if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
			print '</td>';
			print '</tr>';			

			// Status
			print '<tr><td>'.fieldLabel('Status','status',1).'</td><td>';
			print $form->selectarray('status',$status2label,$object->status);
			print '</td></tr>';

            print '</table>';

			dol_fiche_end();

            print '<div class="center">';
			print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
			print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            print '<input type="submit" name="cancel" class="button" value="'.$langs->trans("Cancel").'">';
            print '</div>';

            print '</form>';
        }
        else
        {
            /*
             * Confirm delete
             */
            if ($action == 'delete')
            {
                print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$id,$langs->trans("DeleteEstablishment"),$langs->trans("ConfirmDeleteEstablishment"),"confirm_delete");

            }

        	dol_fiche_head($head, 'card', $langs->trans("Establishment"), 0, 'building');

        	print '<table class="border" width="100%">';

            $linkback = '<a href="../admin/admin_establishment.php">'.$langs->trans("BackToList").'</a>';

            // Ref
            print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td width="50%">';
            print $object->id;
			print '</td><td width="25%">';
			print $linkback;
            print '</td></tr>';

			// Name
			print '<tr>';
			print '<td>'.$langs->trans("Name").'</td>';
			print '<td colspan="2">'.$object->name.'</td>';
			print '</tr>';

			// Address
			print '<tr>';
			print '<td>'.$langs->trans("Address").'</td>';
			print '<td colspan="2">'.$object->address.'</td>';
			print '</tr>';

			// Zipcode
			print '<tr>';
			print '<td>'.$langs->trans("Zipcode").'</td>';
			print '<td colspan="2">'.$object->zip.'</td>';
			print '</tr>';

			// Town
			print '<tr>';
			print '<td>'.$langs->trans("Town").'</td>';
			print '<td colspan="2">'.$object->town.'</td>';
			print '</tr>';

			// Country
			print '<tr>';
			print '<td>'.$langs->trans("Country").'</td>';
			print '<td colspan="2">';
			if ($object->country_id > 0)
			{
				$img=picto_from_langcode($object->country_code);
				print $img?$img.' ':'';
				print getCountry($object->getCountryCode(),0,$db);
			}
			print '</td>';
			print '</tr>';

            // Status
            print '<tr><td>'.$langs->trans("Status").'</td><td colspan="2">'.$object->getLibStatus(4).'</td></tr>';

            print "</table>";

            dol_fiche_end();

            /*
             * Barre d'actions
            */

            print '<div class="tabsAction">';
            print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&id='.$id.'">'.$langs->trans('Modify').'</a>';
			print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?action=delete&id='.$id.'">'.$langs->trans('Delete').'</a>';
            print '</div>';
        }
    }
    else
    {
        dol_print_error($db);
    }
}

llxFooter();

$db->close();
