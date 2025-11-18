mw.loader.using( [ 
        'ext.visualEditor.base', 
        'ext.visualEditor.core', 
        'ext.visualEditor.mediawiki', 
        'ext.visualEditor.data', 
        'ext.visualEditor.desktopArticleTarget.init' 
    ]).then( function () {

    console.log('ClassificationTool VE script loaded');
    
    // Define the tool button in the VE toolbar
    function VEClassificationTool( toolGroup, config ) {
        ve.ui.Tool.call( this, toolGroup, config );
    }

    OO.inheritClass( VEClassificationTool, ve.ui.Tool );

    VEClassificationTool.static.name = 'classificationTool';
    VEClassificationTool.static.group = 'classification';
    VEClassificationTool.static.icon = null;
    VEClassificationTool.static.displayBothIconAndLabel = true;
    VEClassificationTool.static.label = 'Add Classification';
    VEClassificationTool.static.title = 'Add Classification'; 
    VEClassificationTool.static.autoAddToGroup = false;
    VEClassificationTool.static.autoAddToCatchall = false;

    VEClassificationTool.prototype.onUpdateState = function () {
        this.setDisabled( false );
    };
    
    VEClassificationTool.prototype.getBody = function () {
        return new OO.ui.LabelWidget( {
            label: 'Add Classification'
        } );
    };

    VEClassificationTool.prototype.onSelect = function () {
        VEClassificationTool.prototype.onSelect = function () {
            console.log("Classification button clicked");
        
            alert("This tool requires source editing. Please switch to source editor to continue.");
        
            this.setActive(false);
        };
        
    };
    
    
    // Register the tool - disabled for now because it's causing issues
    // ve.ui.toolFactory.register( VEClassificationTool );


    // Add to toolbar group
    ve.init.mw.targetFactory.on( 'register', function ( name, target ) {
        if ( name === 'article' ) {
            target.static.toolbarGroups.push( {
                name: 'classification',
                include: [ 'classificationTool' ]
            } );
        }
    } );
    
    
}() );
