$ErrorActionPreference = "Stop"
$paths = "monaka", "ricopollo", "saas_scary"
foreach ($p in $paths) {
    Set-Location "c:\xampp\htdocs\dulces\$p"
    Write-Host "Instalando dependencias en $p..."
    composer install --no-dev --quiet --no-interaction --optimize-autoloader
    Write-Host "Agregando vendor a Git para $p..."
    git add -f vendor/
}
Set-Location "c:\xampp\htdocs\dulces"
git commit -m "Upload dedicated vendor directories per company to bypass SSH requirement"
Write-Host "Realizando git push..."
git push
Write-Host "Completado."
