#!/usr/bin/env php
<?php
/*
 * @author Anakeen
 * @package FDL
*/
/*
 * Minimal pure-PHP implementation of GNU/parallel
*/

function usage($me)
{
    $usage = <<<EOF

Usage
-----

  $me [-j <max_jobs>] [-f <jobs_file>]


EOF;
    print $usage;
}

function main(&$argv)
{
    $me = array_shift($argv);
    $maxJobs = 1;
    $jobsFile = 'php://stdin';
    while (count($argv) > 0) {
        $arg = array_shift($argv);
        switch ($arg) {
            case '-h':
            case '--help':
                usage($me);
                exit(0);
                break;

            case '-j':
                $maxJobs = array_shift($argv);
                if ($maxJobs === null) {
                    printf("Missing value after '-j' argument!");
                    usage($me);
                    exit(1);
                }
                if ($maxJobs == 'auto') {
                    $maxJobs = getCPUCount();
                }
                $maxJobs = (int)(($maxJobs > 0) ? $maxJobs : 1);
                break;

            case '-f':
                $jobsFile = array_shift($argv);
                if ($jobsFile === null) {
                    printf("Missing file after '-f' argument!");
                    usage($me);
                    exit(1);
                }
                if ($jobsFile === '-') {
                    $jobsFile = 'php://stdin';
                }
                break;

            case '--':
                break 2;
            default:
                printf("Unknown argument '%s'.\n", $arg);
                usage($me);
                exit(1);
        }
    }
    
    $commands = file($jobsFile);
    if ($commands === false) {
        printf("Error reading content from jobs file '%s'!", $jobsFile);
        exit(1);
    }
    
    $globalStatus = true;
    $statusList = array();
    $runningJobs = array();
    foreach ($commands as $command) {
        /* Skip blank lines and (ba)sh comments */
        if (preg_match('/^\s*(#.*)?$/', $command)) {
            continue;
        }
        while (count($runningJobs) >= $maxJobs) {
            waitJob($runningJobs, $statusList);
        }
        $pid = spawnJob($command);
        if ($pid === - 1) {
            printf("Error: spawnJob() returned with unexpected error (running jobs = %d, maxJobs = %d)!", count($runningJobs) , $maxJobs);
            $globalStatus = false;
            break;
        } else {
            $runningJobs[$pid] = $command;
        }
    }
    while (count($runningJobs) > 0) {
        waitJob($runningJobs, $statusList);
    }
    foreach ($statusList as $status) {
        $globalStatus&= ($status === 0);
    }
    exit($globalStatus ? 0 : 1);
}
/**
 * Spawn a new job process to execute the given command and return the job's process pid
 *
 * @param $command
 * @return int
 */
function spawnJob($command)
{
    $pid = pcntl_fork();
    if ($pid === - 1) {
        /* Could not fork a new child process */
        return -1;
    }
    if ($pid !== 0) {
        /* Return the pid to the parent process */
        return $pid;
    }
    /* Execute the command in the new child process */
    passthru($command, $status);
    exit($status);
}
/**
 * Wait and collect a finished job process
 *
 * @param $runningJobs
 * @param $statusList
 */
function waitJob(&$runningJobs, &$statusList)
{
    /* Wait and collect a finished job */
    $pid = pcntl_wait($status);
    if ($pid == - 1) {
        printf("Error: pcntl_wait() returned with unexpected error!");
        exit(1);
    }
    if (!isset($runningJobs[$pid])) {
        printf("Weird: process with PID %d (status %d) is not one of my child...\n", $pid, $status);
    } else {
        unset($runningJobs[$pid]);
        $statusList[] = $status;
    }
}
/**
 * Count number of CPUs
 *
 * @return int The number of CPUs (0 if number of CPUs could not be detected)
 */
function getCPUCount()
{
    $cpuinfo = @file("/proc/cpuinfo");
    if ($cpuinfo === false) {
        return 0;
    }
    return array_reduce($cpuinfo, function ($count, $line)
    {
        if (preg_match('/^processor\s*:\s*\d+$/', $line)) {
            $count++;
        }
        return $count;
    }
    , 0);
}

main($argv);
