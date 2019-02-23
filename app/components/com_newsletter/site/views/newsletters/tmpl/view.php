<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Christopher Smoak <csmoak@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css()
     ->js();
?>


<section class="main section">
  <div class="newsletter-wrap">
    <div class="current-newsletter-wrap">
      <a href="<?php echo Route::url('index.php?option=com_newsletter&task=subscribe'); ?>" class="btn icon-feed">
        <?php echo Lang::txt('COM_NEWSLETTER_VIEW_SUBSCRIBE_TO_MAILINGLISTS'); ?>
      </a>

      <div class="current-newsletter">
        <a href="#">
          <div class="current-img">
            <img src="https://qubeshub.org/app/site/media/images/newsletters/19/01/LDC2019%20banner_blue.png" alt="Newsletter Image">
          </div>
        </a>
        <div class="newsletter-title">
          <a href="#">
            <h3>QUBES Newsletter - January 2019</h3>
          </a>
        </div>
        <div class="newsletter-tag">
          <span>Current</span>
        </div>
      </div>
    </div>

    <div class="past-newsletter-wrapper"> <!-- Start: 4 most recent newsletters -->
      <h2>Latest</h2>
      <hr>
      <div class="past-newsletter-wrap">
        <div class="past-newsletter-content highlight">
          <div class="past-newsletter">
            <a href="#">
              <div class="past-img">
                <img src="https://qubeshub.org/app/site/media/images/newsletters/NIBLSE/01/NIBLSE_logo.png" alt="Image: Current Newsletter">
              </div>
            </a>
            <div class="newsletter-title">
              <a href="#">NIBLSE Newsletter: December 2018</a>
            </div>
            <div class="newsletter-tag">
              <span>Partner News</span>
            </div>
          </div>
        </div>

        <div class="vb"></div>

        <div class="past-newsletter-content">
          <div class="past-newsletter">
            <a href="#">
              <div class="past-img">
                <img src="https://qubeshub.org/app/site/media/images/newsletters/18/12/placeholder_GEA_logo_white_150.png" alt="Image: Recent Newsletter">
              </div>
            </a>
            <div class="newsletter-title">
              <a href="#">QUBES Newsletter - December 2018</a>
            </div>
          </div>
        </div>

        <div class="vb"></div>

        <div class="past-newsletter-content">
          <div class="past-newsletter">
            <a href="#">
              <div class="past-img">
                <img src="https://qubeshub.org/app/site/media/images/newsletters/18/11/Nicole%20Chodkowski.png" alt="Image: Recent Newsletter">
              </div>
            </a>
            <div class="newsletter-title">
              <a href="#">QUBES Newsletter - November 2018</a>
            </div>
          </div>
        </div>

        <div class="vb"></div>

        <div class="past-newsletter-content">
          <div class="past-newsletter">
            <a href="#">
              <div class="past-img">
                <img src="https://qubeshub.org/app/site/media/images/newsletters/18/11/Carrie.png" alt="Image: Recent Newsletter">
              </div>
            </a>
            <div class="newsletter-title">
              <a href="#">QUBES Newsletter - October 2018</a>
            </div>
          </div>
        </div>
      </div> <!-- End: recent newsletter wrap -->
    </div> <!-- End: recent newsletters -->
  </div> <!-- End: newsletter wrap -->

  <div class="row-newsletter-wrapper"> <!-- Start: ROW section -->
    <h2>Resource of the Week</h2>
    <hr>
    <span>Check out <a href="https://qubeshub.org/news/newsletter/row">all past</a> resources featured in ROW!</span>
    <div class="row-newsletter-wrap">
      <div class="row-newsletter">
        <div class="row-img">
          <a href="#">
            <img src="https://qubeshub.org/app/site/media/images/newsletters/ROW/22/1200px-Bike_path_on_College_in_Toronto-3009.jpeg" alt="Image: Resource of the Week">
          </a>
        </div>
        <div class="row-title">
          <h3><a href="#">Working with spreadsheet-style data in Python with pandas and seaborn (Version 1.0)</a></h3>
        </div>
      </div>

      <div class="row-newsletter">
        <div class="row-img">
          <a href="#">
            <img src="https://qubeshub.org/app/site/media/images/newsletters/ROW/021/Screenshot%20from%202018-10-30%2002-58-31-2690.png" alt="Image: Resource of the Week">
          </a>
        </div>
        <div class="row-title">
          <h3><a href="#">Statistical Exploration of Climate Data (Version 1.0)</a></h3>
        </div>
      </div>

      <div class="row-newsletter">
        <div class="row-img">
          <a href="#">
            <img src="https://qubeshub.org/app/site/media/images/newsletters/ROW/020/AntPicture-1628.jpg" alt="Image: Resource of the Week">
          </a>
        </div>
        <div class="row-title">
          <h3><a href="#">Leaf cutter ant foraging (Version 1.0)</a></h3>
        </div>
      </div>

      <div class="row-newsletter">
        <div class="row-img">
          <a href="#">
            <img src="https://qubeshub.org/app/site/media/images/newsletters/ROW/019/gslogo-print%20copy-2480.jpg" alt="Image: Resource of the Week">
          </a>
        </div>
        <div class="row-title">
          <h3><a href="#">Genome Solver - Complete Set of Lessons</a></h3>
        </div>
      </div>
    </div>
  </div> <!-- End: ROW section -->

  <div class="partner-newsletter-wrapper"> <!-- Start: Partner Newsletter -->
    <h2>Partner Newsletters</h2>
    <hr>
    <div class="partner-newsletter-wrap">
      <div class="partner-newsletter">
        <div class="partner-img">
          <a href="#">
            <img src="https://qubeshub.org/app/site/media/images/newsletters/NIBLSE/01/NIBLSE_logo.png" alt="Image: Partner Newsletter">
          </a>
        </div>
        <div class="partner-title">
          <h3><a href="#">NIBLSE Newsletter: December 2018</a></h3>
        </div>
      </div>

      <!-- Ghosting 3 fillers to keep one entry aligned with ROW on window resizing. Fillers will need to be removed as this section fills up as well as CSS updating updating -->

      <div class="partner-newsletter">
        <div class="partner-img">
          <a href="#">
            <!-- <img src="https://qubeshub.org/app/site/media/images/newsletters/NIBLSE/01/NIBLSE_logo.png" alt="Image: Partner Newsletter"> -->
          </a>
        </div>
        <!-- <div class="partner-title">
          <h3><a href="#">NIBLSE Newsletter: December 2018</a></h3>
        </div> -->
      </div>

      <div class="partner-newsletter">
        <div class="partner-img">
          <a href="#">
            <!-- <img src="https://qubeshub.org/app/site/media/images/newsletters/NIBLSE/01/NIBLSE_logo.png" alt="Image: Partner Newsletter"> -->
          </a>
        </div>
        <!-- <div class="partner-title">
          <h3><a href="#">NIBLSE Newsletter: December 2018</a></h3>
        </div> -->
      </div>

      <div class="partner-newsletter">
        <div class="partner-img">
          <a href="#">
            <!-- <img src="https://qubeshub.org/app/site/media/images/newsletters/NIBLSE/01/NIBLSE_logo.png" alt="Image: Partner Newsletter"> -->
          </a>
        </div>
        <!-- <div class="partner-title">
          <h3><a href="#">NIBLSE Newsletter: December 2018</a></h3>
        </div> -->
      </div>
    </div>
  </div> <!-- End: Partner Newsletter -->
</section>
