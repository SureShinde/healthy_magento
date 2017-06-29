<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
?>
<?php
/**
 * Customer login form template
 *
 * @see app/design/frontend/base/default/template/customer/form/login.phtml
 */
/** @var $this Mage_Customer_Block_Form_Login */
?>



<div class="account-login">
    <div class="block block-login reset-block-login">
        <ul class="slides">
            <li>
                <div class="block-title"> <strong><span>Please reset your password</span></strong> </div>
                <div class="block-content">
                    <ul class="form-list">
                        <li>
                            <label for="email">With the new <a href="/">HackleyHealthManagement.com</a> website redesign, all existing customers must perform a one-time password reset in order to access any restricted areas or conduct any transactions on the website. Thank you!</label>
                        </li>
                    </ul>
          
                    <div class="thankyou-ques">
                        <br />
                        <p style="font-size: 16px;">If you have any questions or concerns, please call us at:</p>
                        <h2>800.521.9054</h2>
                    </div>
                
            </div>
            <div class="new-users"><img src="https://www.bandsawbladesdirect.com/skin/frontend/default/shopper/images/godaddy.jpg"> </div>
            </li>

        </ul>
    </div>




    <div class="block block-login reset-block-login-right">
        <div class="block-slider">
            <ul class="slides">
                <li>
                    <div class="block-title">
                        <strong><span><?php echo $this->__('Sign In') ?></span></strong>
                    </div>
                    <div class="block-content">
                        <?php echo $this->getMessagesBlock()->getGroupedHtml() ?>
                        <form action="<?php echo $this->helper('customer')->getLoginPostUrl() ?>" method="post" id="login-form">
                            <ul class="form-list">
                                <li>
                                    <label for="email" class="required"><?php echo $this->__('Email Address') ?></label>
                                    <div class="input-box">
                                        <input type="text" name="login[username]" value="<?php echo $this->htmlEscape($this->getUsername()) ?>" id="email" class="input-text required-entry validate-email" title="<?php echo $this->__('Email Address') ?>" />
                                    </div>
                                </li>
                                <li>
                                    <label for="pass" class="required"><?php echo $this->__('Password') ?></label>
                                    <div class="input-box">
                                        <input type="password" name="login[password]" class="input-text required-entry validate-password" id="pass" title="<?php echo $this->__('Password') ?>" />
                                    </div>
                                </li>
                                <?php echo $this->getChildHtml('form.additional.info'); ?>
                                <?php echo $this->getChildHtml('persistent.remember.me'); ?>
                            </ul>
                            <?php echo $this->getChildHtml('persistent.remember.me.tooltip'); ?>
                            <button type="submit" class="button" title="<?php echo $this->__('Login') ?>" name="send" id="send2"><span><span><?php echo $this->__('Login') ?></span></span></button>
                            <a href="<?php echo $this->getForgotPasswordUrl() ?>" class="forgot-password" id="forgot-password"><?php echo $this->__('Forgot Your Password?') ?></a>

                            <?php if (Mage::helper('checkout')->isContextCheckout()): ?>
                            <input name="context" type="hidden" value="checkout" />
                            <?php endif; ?>
                        </form>
                    </div>
                </li>
                <li>
                    <div class="block-title">
                        <strong><span><?php echo $this->__('Reset Your Password?') ?></span></strong>
                    </div>
                    <div class="block-content">
                        <form action="<?php echo $this->getUrl('*/*/forgotpasswordpost') ?>" method="post" id="form-validate">
                            <ul class="form-list">
                                <li>
                                    <label for="email" class="required">In order to reset your password, please enter your email address below. You will then receive an email containing a link to reset your password shortly. If you do not receive this email in a timely matter, please call us at<br /><span style="font-weight: bold;font-size: 18px;color:#4576A2;" >800.521.9054</span></label>
                                    <div class="input-box">
                                        <input type="text" name="email" alt="email" id="email_address" class="input-text required-entry validate-email" value="<?php echo $this->htmlEscape($this->getEmailValue()) ?>" />
                                    </div>
                                </li>
                                <?php echo $this->getChildHtml('form.additional.info'); ?>
                            </ul>
                            <button type="submit" class="button" title="<?php echo $this->__('Submit') ?>"><span><span><?php echo $this->__('Submit') ?></span></span></button>
                            <!--<a href="<?php echo $this->getForgotPasswordUrl() ?>" class="forgot-password" id="back-login"><?php echo $this->__('Back to Login?'); ?></a>-->
                        </form>
                    </div>
                </li>
            </ul>
        </div>
        <div class="new-users new-user1">
            <div class="block-title block-password"> <strong><span>Already reset your password?</span></strong> </div>
            <button onclick="window.location='<?php echo $this->getUrl();?>customer/account/login/'" title="login now" class="button" type="button"><span><span>login now</span></span></button>
        </div>
        <div class="new-users new-user1">
            <div class="block-title block-password"> <strong><span>New to our website?</span></strong> </div>
            <button type="button" title="<?php echo $this->__('Create an Account') ?>" class="button invert" onclick="window.location='<?php echo $this->helper('customer')->getRegisterUrl(); ?>';"><span><span><?php echo $this->__('Create an Account') ?></span></span></button>
        </div>
    </div>

    <script type="text/javascript">
        //<![CDATA[
        var dataForm = new VarienForm('login-form', true);
        var dataForgetForm = new VarienForm('form-validate', true);
        //]]>
    </script>
</div>
