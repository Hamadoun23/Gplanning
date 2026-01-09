# Script PowerShell pour creer les icones PWA a partir de Icones.jpg
# Necessite .NET Framework ou .NET Core

$sourceImage = Join-Path "public" "Icones.jpg"
$outputDir = "public"
$sizes = @(72, 96, 128, 144, 152, 192, 384, 512)

# Verifier si le fichier source existe
if (-not (Test-Path $sourceImage)) {
    Write-Host "Erreur: Le fichier Icones.jpg n'existe pas dans public!" -ForegroundColor Red
    exit 1
}

Write-Host "Creation des icones PWA a partir de Icones.jpg..." -ForegroundColor Green
Write-Host ""

try {
    # Charger l'assembly System.Drawing
    Add-Type -AssemblyName System.Drawing
    
    # Charger l'image source
    $source = [System.Drawing.Image]::FromFile((Resolve-Path $sourceImage).Path)
    $sourceWidth = $source.Width
    $sourceHeight = $source.Height
    
    Write-Host "Source: ${sourceWidth}x${sourceHeight}px" -ForegroundColor Cyan
    Write-Host ""
    
    foreach ($size in $sizes) {
        # Creer une nouvelle image carree
        $bitmap = New-Object System.Drawing.Bitmap($size, $size)
        $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
        
        # Configuration pour une meilleure qualite
        $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
        $graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
        $graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
        
        # Calculer les dimensions pour un crop centre (carre)
        $sourceRatio = $sourceWidth / $sourceHeight
        
        if ($sourceRatio -gt 1) {
            # L'image est plus large que haute
            $cropSize = $sourceHeight
            $x = ($sourceWidth - $cropSize) / 2
            $y = 0
        } else {
            # L'image est plus haute que large
            $cropSize = $sourceWidth
            $x = 0
            $y = ($sourceHeight - $cropSize) / 2
        }
        
        # Dessiner l'image redimensionnee
        $graphics.DrawImage($source, 0, 0, $size, $size)
        
        # Sauvegarder en PNG
        $outputPath = Join-Path $outputDir "icon-${size}x${size}.png"
        $bitmap.Save($outputPath, [System.Drawing.Imaging.ImageFormat]::Png)
        
        # Liberer les ressources
        $graphics.Dispose()
        $bitmap.Dispose()
        
        Write-Host "Cree: icon-${size}x${size}.png" -ForegroundColor Green
    }
    
    # Liberer l'image source
    $source.Dispose()
    
    Write-Host ""
    Write-Host "Toutes les icones PWA ont ete creees avec succes!" -ForegroundColor Green
    Write-Host "Les icones sont disponibles dans le dossier public" -ForegroundColor Cyan
    Write-Host "Note: Le logo.png dans les pages reste inchange." -ForegroundColor Yellow
    
} catch {
    Write-Host "Erreur: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Solution alternative:" -ForegroundColor Yellow
    Write-Host "1. Allez sur https://www.pwabuilder.com/imageGenerator" -ForegroundColor Cyan
    Write-Host "2. Telechargez public/Icones.jpg" -ForegroundColor Cyan
    Write-Host "3. Generez les icones et extrayez-les dans public/" -ForegroundColor Cyan
    exit 1
}
