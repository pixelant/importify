
# Module configuration
module.tx_importify_tools_importifyimportdata {
    persistence {
        storagePid = {$module.tx_importify_importdata.persistence.storagePid}
    }
    view {
        templateRootPaths.0 = EXT:importify/Resources/Private/Backend/Templates/
        templateRootPaths.1 = {$module.tx_importify_importdata.view.templateRootPath}
        partialRootPaths.0 = EXT:importify/Resources/Private/Backend/Partials/
        partialRootPaths.1 = {$module.tx_importify_importdata.view.partialRootPath}
        layoutRootPaths.0 = EXT:importify/Resources/Private/Backend/Layouts/
        layoutRootPaths.1 = {$module.tx_importify_importdata.view.layoutRootPath}
    }
}
