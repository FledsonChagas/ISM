<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

namespace Glpi\Console\Security;

use Glpi\Console\AbstractCommand;
use GLPIKey;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangekeyCommand extends AbstractCommand
{
   /**
    * Error code returned when unable to renew key.
    *
    * @var integer
    */
    const ERROR_UNABLE_TO_RENEW_KEY = 1;

    protected function configure()
    {
        parent::configure();

        $this->setName('glpi:security:change_key');
        $this->setDescription(__('Change password storage key and update values in database.'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $glpikey = new GLPIKey();

        $fields = $glpikey->getFields();
        $configs = $glpikey->getConfigs();
        $conf_count = 0;
        foreach ($configs as $config) {
            $conf_count += count($config);
        }

        $output->writeln(
            sprintf(
                '<info>' . __('Found %1$s field(s) and %2$s configuration entries requiring migration.') . '</info>',
                count($fields),
                $conf_count
            )
        );

        $this->askForConfirmation();

        $created = $glpikey->generate();
        if (!$created) {
            $output->writeln(
                '<error>' . __('Unable to change security key!') . '</error>',
                OutputInterface::VERBOSITY_QUIET
            );
            return self::ERROR_UNABLE_TO_RENEW_KEY;
        }

        $this->output->write(PHP_EOL);

        $output->writeln('<info>' . __('New security key generated; database updated.') . '</info>');

        return 0; // Success
    }
}
