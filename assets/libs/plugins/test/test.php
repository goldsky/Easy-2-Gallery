<?php
if (!isset($e2gEvtName))
    return;

switch ($e2gEvtName) {

    /**
     * For web-page
     */
    case 'OnE2GWebThumbPrerender':
        echo 'test PLUGIN : OnE2GWebThumbPrerender';
        break;

    case 'OnE2GWebThumbRender':
        echo 'test PLUGIN : OnE2GWebThumbRender';
        break;

    case 'OnE2GWebDirPrerender':
        echo 'test PLUGIN : OnE2GWebDirPrerender';
        break;

    case 'OnE2GWebDirRender':
        echo 'test PLUGIN : OnE2GWebDirRender';
        break;

    case 'OnE2GWebGalleryPrerender':
        echo 'test PLUGIN : OnE2GWebGalleryPrerender';
        break;

    case 'OnE2GWebGalleryRender':
        echo 'test PLUGIN : OnE2GWebGalleryRender';
        break;

    case 'OnE2GWebLandingpagePrerender':
        echo 'test PLUGIN : OnE2GWebLandingpagePrerender';
        break;

    case 'OnE2GWebLandingpageRender':
        echo 'test PLUGIN : OnE2GWebLandingpageRender';
        break;

    /**
     * For module
     */
    /**
     * Header
     */
    case 'OnE2GModHeadScript':
//        echo 'TEST PLUGIN : OnE2GModHeadScript';
        break;

    case 'OnE2GModHeadCSSScript':
//        echo 'TEST PLUGIN : OnE2GModHeadCSSScript';
        break;

    case 'OnE2GModHeadJSScript':
//        echo 'TEST PLUGIN : OnE2GModHeadJSScript';
        break;

    /**
     * Dashboard
     */
    case 'OnE2GDashboardPrerender':
        echo 'TEST PLUGIN : OnE2GDashboardPrerender';
        break;

    case 'OnE2GDashboardRender':
        echo 'TEST PLUGIN : OnE2GDashboardRender';
        break;

    /**
     * Files manager
     */
    case 'OnE2GFolderCreateFormPrerender':
        echo 'TEST PLUGIN : OnE2GFolderCreateFormPrerender';
        break;

    case 'OnE2GFolderCreateFormRender':
?>
        <div class="tab-page" id="tpCreateFolderTest">
            <h2 class="tab">TEST</h2>
            <script type="text/javascript">
                tpCreateFolder.addTabPage( document.getElementById( 'tpCreateFolderTest') );
            </script>
            <p>OnE2GFolderCreateFormRender</p>
        </div>
<?php
        echo 'TEST PLUGIN : OnE2GFolderCreateFormRender';
        break;

    case 'OnE2GFolderCreateFormSave':
        $_SESSION['easy2err'][] = 'TEST PLUGIN : OnE2GFolderCreateFormSave';
        break;

    case 'OnE2GFolderAdd':
        $_SESSION['easy2err'][] = 'TEST PLUGIN : OnE2GFolderAdd';
        break;

    case 'OnE2GFolderEditFormPrerender':
        echo 'TEST PLUGIN : OnE2GFolderEditFormPrerender';
        break;

    case 'OnE2GFolderEditFormPrerender':
        echo 'TEST PLUGIN : OnE2GFolderEditFormPrerender';
        break;

    case 'OnE2GFolderEditFormRender':
?>
        <div class="tab-page" id="tpEditFolderTest">
            <h2 class="tab">TEST</h2>
            <script type="text/javascript">
                tpEditFolder.addTabPage( document.getElementById( 'tpEditFolderTest') );
            </script>
            <p>OnE2GFolderEditFormRender</p>
        </div>
<?php
        echo 'TEST PLUGIN : OnE2GFolderEditFormRender';
        break;

    case 'OnE2GFolderEditFormSave':
        $_SESSION['easy2err'][] = 'TEST PLUGIN : OnE2GFolderEditFormSave';
        break;

    case 'OnE2GFolderDelete':
        $_SESSION['easy2err'][] = 'TEST PLUGIN : OnE2GFolderDelete';
        break;

    case 'OnE2GFileUploadFormPrerender':
        echo 'TEST PLUGIN : OnE2GFileUploadFormPrerender';
        break;

    case 'OnE2GFileUploadFormRender':
?>
        <div class="tab-page" id="tabFileUploadTest">
            <h2 class="tab">TEST 1</h2>
            <script type="text/javascript">
                tpFileUpload.addTabPage( document.getElementById( 'tabFileUploadTest') );
            </script>
            <p>OnE2GFileUploadFormRender</p>
        </div>
<?php
        echo 'TEST PLUGIN : OnE2GFileUploadFormRender';
        break;

    case 'OnE2GFileUploadSubFormRender':
?>
        <div class="tab-page" id="tabUploaderTest">
            <h2 class="tab">TEST 2</h2>
            <script type="text/javascript">
                tpUploader.addTabPage( document.getElementById('tabUploaderTest') );
            </script>
            <p>OnE2GFileUploadSubFormRender</p>
        </div>
<?php
        echo 'TEST PLUGIN : OnE2GFileUploadSubFormRender';
        break;

    case 'OnE2GFileUpload':
        $_SESSION['easy2err'][] = 'TEST PLUGIN : OnE2GFileUpload';
        break;

    case 'OnE2GFileEditFormPrerender':
        echo 'TEST PLUGIN : OnE2GFileEditFormPrerender';
        break;

    case 'OnE2GFileEditFormRender':
?>
        <div class="tab-page" id="tabFileEditTest">
            <h2 class="tab">TEST</h2>
            <script type="text/javascript">
                tpEditFile.addTabPage( document.getElementById( 'tabFileEditTest') );
            </script>
            <p>OnE2GFileEditFormRender</p>
        </div>
<?php
        break;

    case 'OnE2GFileEditFormSave':
        $_SESSION['easy2err'][] = 'TEST PLUGIN : OnE2GFileEditFormSave';
        break;

    case 'OnE2GFileDelete':
        $_SESSION['easy2err'][] = 'TEST PLUGIN : OnE2GFileDelete';
        break;

    case 'OnE2GZipUploadFormPrerender':
        echo 'TEST PLUGIN : OnE2GZipUploadFormPrerender';
        break;

    case 'OnE2GZipUploadFormRender':
        echo 'TEST PLUGIN : OnE2GZipUploadFormRender';
        break;

    case 'OnE2GZipUpload':
        $_SESSION['easy2err'][] = 'TEST PLUGIN : OnE2GZipUpload';
        break;

    case 'OnE2GCommentManagerPrerender':
        echo 'TEST PLUGIN : OnE2GCommentManagerPrerender';
        break;

    default:
        break;
}