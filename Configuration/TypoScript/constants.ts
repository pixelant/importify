
module.tx_importify_importdata {
    view {
        # cat=module.tx_importify_importdata/file; type=string; label=Path to template root (BE)
        templateRootPath = EXT:importify/Resources/Private/Backend/Templates/
        # cat=module.tx_importify_importdata/file; type=string; label=Path to template partials (BE)
        partialRootPath = EXT:importify/Resources/Private/Backend/Partials/
        # cat=module.tx_importify_importdata/file; type=string; label=Path to template layouts (BE)
        layoutRootPath = EXT:importify/Resources/Private/Backend/Layouts/
    }
    persistence {
        # cat=module.tx_importify_importdata//a; type=string; label=Default storage PID
        storagePid =
    }
}

## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder