<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices();
?>

<aside class="sidebar">
    <?php do_action( 'woocommerce_account_navigation' ); ?>
</aside><!-- /.sidebar -->

<div class="content">

	<?php
	/**
	 * My Account content.
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_content' );
	?>

    <?php /**
    <div class="article-single article-orders">
        <h1>WELCOME</h1>

        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse pretium ullamcorper porttitor. Nam molestie nibh justo, pulvinar fermentum elit lacinia vel. Vestibulum sed nibh tempus, sagittis ipsum at.</p>

        <div class="table table--orders">
            <div class="table__head">
                <h4>RECENT ORDERS</h3>
            </div><!-- /.table__head -->

            <div class="table__body">
                <table>
                    <tr>
                        <th>Order #</th>
                        <th>DATE</th>
                        <th>Status</th>
                        <th>total</th>
                        <th colspan="3">items</th>
                    </tr>

                    <tr class="order">
                        <td data-title="order #">#8652</td>
                        <td data-title="date">15 Aug 2017</td>
                        <td data-title="status">Awaiting Fulfillmet</td>
                        <td data-title="items">$1,200.00</td>
                        <td data-title="total">15</td>
                        <td>
                            <a href="#" class="link-more">
                                <span>VIEW ITEMS</span>

                                <span class="arrow"></span>
                            </a>
                        </td>
                        <td>
                            <ul>
                                <li>
                                    <a href="#" class="btn">VIEW ORDER</a>
                                </li>

                                <li>
                                    <a href="#" class="btn btn--grey">REORDER</a>
                                </li>
                            </ul>
                        </td>
                    </tr>

                    <tr class="order-details">
                        <td colspan="7">
                            <div class="table">
                                <div class="table__head">
                                    <h5>your order contains</h5>
                                </div><!-- /.table__head -->
                                <div class="table__body">
                                    <table>
                                        <tr>
                                            <th>Product</th>
                                            <th>ITEM PRICE</th>
                                            <th>QTY</th>
                                            <th>TOTAL</th>
                                        </tr>

                                        <tr>
                                            <td>
                                                Wall mount wire brochure holder

                                                <span>Code: B-WMWBHA43</span>
                                            </td>
                                            <td data-title="ITEM PRICE">$50.00</td>
                                            <td data-title="QTY">6</td>
                                            <td data-title="TOTAL">$300.00</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                BROCHURE HOLDER FREE STANDING A4

                                                <span>Code: B-FSA4</span>
                                            </td>
                                            <td data-title="ITEM PRICE">$50.00</td>
                                            <td data-title="QTY">4</td>
                                            <td data-title="TOTAL">$200.00</td>
                                        </tr>
                                    </table>
                                </div><!-- /.table__body -->
                            </div><!-- /.table -->
                        </td>
                    </tr>

                    <tr class="order">
                        <td data-title="order #">#8651</td>
                        <td data-title="date">10 Aug 2017</td>
                        <td data-title="status">Awaiting Fulfillmet</td>
                        <td data-title="items">$500.00</td>
                        <td data-title="total">2</td>
                        <td>
                            <a href="#" class="link-more">
                                <span>VIEW ITEMS</span>

                                <span class="arrow"></span>
                            </a>
                        </td>
                        <td>
                            <ul>
                                <li>
                                    <a href="#" class="btn">VIEW ORDER</a>
                                </li>

                                <li>
                                    <a href="#" class="btn btn--grey">REORDER</a>
                                </li>
                            </ul>
                        </td>
                    </tr>

                    <tr class="order-details">
                        <td colspan="7">
                            <div class="table">
                                <div class="table__head">
                                    <h5>your order contains</h5>
                                </div><!-- /.table__head -->
                                <div class="table__body">
                                    <table>
                                        <tr>
                                            <th>Product</th>
                                            <th>ITEM PRICE</th>
                                            <th>QTY</th>
                                            <th>TOTAL</th>
                                        </tr>

                                        <tr>
                                            <td>
                                                Wall mount wire brochure holder

                                                <span>Code: B-WMWBHA43</span>
                                            </td>
                                            <td data-title="ITEM PRICE">$50.00</td>
                                            <td data-title="QTY">6</td>
                                            <td data-title="TOTAL">$300.00</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                BROCHURE HOLDER FREE STANDING A4

                                                <span>Code: B-FSA4</span>
                                            </td>
                                            <td data-title="ITEM PRICE">$50.00</td>
                                            <td data-title="QTY">4</td>
                                            <td data-title="TOTAL">$200.00</td>
                                        </tr>
                                    </table>
                                </div><!-- /.table__body -->
                            </div><!-- /.table -->
                        </td>
                    </tr>

                    <tr class="order">
                        <td data-title="order #">#8650</td>
                        <td data-title="date">05 Aug 2017</td>
                        <td data-title="status">Shipped</td>
                        <td data-title="items">$200.00</td>
                        <td data-title="total">10</td>
                        <td>
                            <a href="#" class="link-more">
                                <span>VIEW ITEMS</span>

                                <span class="arrow"></span>
                            </a>
                        </td>
                        <td>
                            <ul>
                                <li>
                                    <a href="#" class="btn">VIEW ORDER</a>
                                </li>

                                <li>
                                    <a href="#" class="btn btn--grey">REORDER</a>
                                </li>
                            </ul>
                        </td>
                    </tr>

                    <tr class="order-details">
                        <td colspan="7">
                            <div class="table">
                                <div class="table__head">
                                    <h5>your order contains</h5>
                                </div><!-- /.table__head -->
                                <div class="table__body">
                                    <table>
                                        <tr>
                                            <th>Product</th>
                                            <th>ITEM PRICE</th>
                                            <th>QTY</th>
                                            <th>TOTAL</th>
                                        </tr>

                                        <tr>
                                            <td>
                                                Wall mount wire brochure holder

                                                <span>Code: B-WMWBHA43</span>
                                            </td>
                                            <td data-title="ITEM PRICE">$50.00</td>
                                            <td data-title="QTY">6</td>
                                            <td data-title="TOTAL">$300.00</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                BROCHURE HOLDER FREE STANDING A4

                                                <span>Code: B-FSA4</span>
                                            </td>
                                            <td data-title="ITEM PRICE">$50.00</td>
                                            <td data-title="QTY">4</td>
                                            <td data-title="TOTAL">$200.00</td>
                                        </tr>
                                    </table>
                                </div><!-- /.table__body -->
                            </div><!-- /.table -->
                        </td>
                    </tr>

                    <tr class="order">
                        <td data-title="order #">#8649</td>
                        <td data-title="date">20 Jul 2017</td>
                        <td data-title="status">Shipped</td>
                        <td data-title="items">$120.00</td>
                        <td data-title="total">5</td>
                        <td>
                            <a href="#" class="link-more">
                                <span>VIEW ITEMS</span>

                                <span class="arrow"></span>
                            </a>
                        </td>
                        <td>
                            <ul>
                                <li>
                                    <a href="#" class="btn">VIEW ORDER</a>
                                </li>

                                <li>
                                    <a href="#" class="btn btn--grey">REORDER</a>
                                </li>
                            </ul>
                        </td>
                    </tr>

                    <tr class="order-details">
                        <td colspan="7">
                            <div class="table">
                                <div class="table__head">
                                    <h5>your order contains</h5>
                                </div><!-- /.table__head -->
                                <div class="table__body">
                                    <table>
                                        <tr>
                                            <th>Product</th>
                                            <th>ITEM PRICE</th>
                                            <th>QTY</th>
                                            <th>TOTAL</th>
                                        </tr>

                                        <tr>
                                            <td>
                                                Wall mount wire brochure holder

                                                <span>Code: B-WMWBHA43</span>
                                            </td>
                                            <td data-title="ITEM PRICE">$50.00</td>
                                            <td data-title="QTY">6</td>
                                            <td data-title="TOTAL">$300.00</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                BROCHURE HOLDER FREE STANDING A4

                                                <span>Code: B-FSA4</span>
                                            </td>
                                            <td data-title="ITEM PRICE">$50.00</td>
                                            <td data-title="QTY">4</td>
                                            <td data-title="TOTAL">$200.00</td>
                                        </tr>
                                    </table>
                                </div><!-- /.table__body -->
                            </div><!-- /.table -->
                        </td>
                    </tr>
                </table>
            </div><!-- /.table__body -->
        </div><!-- /.table -->
    </div><!-- /.article-single --> */ ?>
</div><!-- /.content -->
