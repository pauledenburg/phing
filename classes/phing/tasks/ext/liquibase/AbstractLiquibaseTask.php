<?php

/**
 * Copyright (c) 2007-2011 bitExpert AG
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

require_once 'phing/Task.php';
require_once 'phing/tasks/system/ExecTask.php';

/**
 * Abstract Liquibase task. Base class for all Liquibase Phing tasks.
 *
 * @author Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @version $Id$
 * @since 2.4.10
 * @package phing.tasks.ext.liquibase
 */
abstract class AbstractLiquibaseTask extends Task
{
    protected $jar;
    protected $changeLogFile;
    protected $username;
    protected $password;
    protected $url;
    protected $classpathref;

    /**
     * Whether to display the output of the command.
     * True by default to preserve old behaviour
     * @var boolean
     */
    private $display = true;

    /**
     * Whether liquibase return code can cause a Phing failure.
     * @var boolean
     */
    private $checkreturn = false;

    /**
      * Set true if we should run liquibase with PHP passthru 
      * instead of exec.
      */
    private $passthru = true;

    /**
     * Sets the absolute path to liquibase jar.
     *
     * @param string the absolute path to the liquibase jar.
     */
    public function setJar($jar)
    {
        $this->jar = $jar;
    }


    /**
     * Sets the absolute path to the changelog file to use.
     *
     * @param string the absolute path to the changelog file
     */
    public function setChangeLogFile($changelogFile)
    {
        $this->changeLogFile = $changelogFile;
    }


    /**
     * Sets the username to connect to the database.
     *
     * @param string the username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }


    /**
     * Sets the password to connect to the database.
     *
     * @param string the password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }


    /**
     * Sets the url to connect to the database in jdbc style, e.g.
     * <code>
     * jdbc:postgresql://psqlhost/mydatabase
     * </code>
     *
     * @param string jdbc connection string
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }


    /**
     * Sets the Java classpathref.
     *
     * @param string A reference to the classpath that contains the database
     * 					driver, liquibase.jar, and the changelog.xml file
     */
    public function setclasspathref($classpathref)
    {
        $this->classpathref = $classpathref;
    }


    /**
     * Sets whether to display the output of the command
     * @param boolean $display
     */
    public function setDisplay($display)
    {
        $this->display = StringHelper::booleanValue($display);
    }


    /**
     * Whether to check the liquibase return code.
     *
     * @param boolean $checkreturn
     */
    public function setCheckreturn($checkreturn)
    {
        $this->checkreturn = StringHelper::booleanValue($checkreturn);
    }


    /**
     * Whether to check the liquibase return code.
     *
     * @param boolean $checkreturn
     */
    public function setPassthru($passthru)
    {
        $this->passthru = StringHelper::booleanValue($passthru);
    }

    /**
     * Ensure that correct parameters were passed in.
     *
     * @return void
     */
    protected function checkParams()
    {
        if((null === $this->jar) or !file_exists($this->jar))
        {
            throw new BuildException(
            sprintf(
					'Specify the name of the LiquiBase.jar. "%s" does not exist!',
            $this->jar
            )
            );
        }

        if((null === $this->changeLogFile) or !file_exists($this->changeLogFile))
        {
            throw new BuildException(
            sprintf(
					'Specify the name of the Changelog file. "%s" does not exist!',
            $this->changeLogFile
            )
            );
        }

        if(null === $this->classpathref)
        {
            throw new BuildException('Please provide a classpath!');
        }

        if(null === $this->username)
        {
            throw new BuildException('Please provide a username for database acccess!');
        }

        if(null === $this->password)
        {
            throw new BuildException('Please provide a password for database acccess!');
        }

        if(null === $this->url)
        {
            throw new BuildException('Please provide a url for database acccess!');
        }
    }


    /**
     * Executes the given command and returns the output.
     *
     * @param string the command to execute
     * @param string additional parameters
     * @return string the output of the executed command
     */
    protected function execute($lbcommand, $lbparams = '')
    {
        $command = sprintf(
			'java -jar %s --changeLogFile=%s --url=%s --username=%s --password=%s --classpath=%s %s %s 2>&1',
        escapeshellarg($this->jar),
        escapeshellarg($this->changeLogFile),
        escapeshellarg($this->url),
        escapeshellarg($this->username),
        escapeshellarg($this->password),
        escapeshellarg($this->classpathref),
        escapeshellarg($lbcommand),
        $lbparams
        );
        
        if( $this->passthru ) 
        {
            passthru($command);
        }
        else {
            $output = array();
            $return = null;
            exec($command, $output, $return);

            if( $this->display ) {
                print implode(PHP_EOL,$output);
            }

            if( $this->checkreturn && $return != 0 ) {
                throw new BuildException("Liquibase exited with code $return");
            }
        }

        return;
    }
}
