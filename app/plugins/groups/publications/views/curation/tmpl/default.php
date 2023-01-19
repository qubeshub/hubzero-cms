<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
$this->css('curation'); 
?>

<div class="group-curation-body">
    <div class="group-curation-menu">
        <ul class="static-menu">
            <li><a href="#">All</a> (#)</li>
            <!-- Might not even need this if you can only see what you have access to -->
            <li><a href="#">Assigned to me</a> (#)</li>
        </ul>
        <h4 class="header-underline">Pending</h4>
        <ul class="pending-menu">
            <li><a href="#">Quality Check</a> (#)</li>
            <li><a href="#">Editor Assignment</a> (#)</li>
            <li><a href="#">Reviewer Assignment</a> (#)</li>
            <li><a href="#">Editor Decision</a> (#)</li>
            <li><a href="#">Post Acceptance Check</a> (#)</li>
        </ul>
        <h4><a href="#">Settings</a></h4>
    </div>

    <div class="group-curation-main">
        <table>
            <thead>
                <tr>
                    <th><a href="#" class="sort-by">ID</a></th>
                    <th><a href="#" class="sort-by">Title</a></th>
                    <th><a href="#" class="sort-by">Author</a></th>
                    <th><a href="#" class="sort-by">Submitted</a></th>
                    <th><a href="#" class="sort-by">Status</a></th>
                    <th><a href="#" class="sort-by">Days in Processing</a></th>
                </tr>
            </thead>
            <tbody>
                <tr class="odd">
                    <td>122</td>
                    <td><a href="#">Title of Something Neat</a></td>
                    <td>Author Authorson</td>
                    <td>18 Jan 2023</td>
                    <td>Pending Quality Check</td>
                    <td>0</td>
                </tr>
                <tr class="even">
                    <td>123</td>
                    <td><a href="#">Title of Big Doomy Doom</a></td>
                    <td>Frank Frankfert</td>
                    <td>18 Jan 2023</td>
                    <td>Pending Quality Check</td>
                    <td>0</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>