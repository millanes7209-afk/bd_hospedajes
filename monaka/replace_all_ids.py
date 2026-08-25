import os
import sys

views_dir = r"c:\xampp\htdocs\dulces\monaka\resources\views"
app_dir = r"c:\xampp\htdocs\dulces\monaka\app"

replacements = {
    'productoID': 'producto_id',
    'varianteID': 'variante_id',
    'categoriaID': 'categoria_id',
    'mesaID': 'mesa_id',
    'pedidoID': 'pedido_id',
    'ventaID': 'venta_id',
    'rolID': 'rol',
    'usuarioID': 'usuario_id'
}

count = 0
for d in [views_dir, app_dir]:
    for root, dirs, files in os.walk(d):
        for f in files:
            if f.endswith('.php'):
                path = os.path.join(root, f)
                with open(path, 'r', encoding='utf-8') as file:
                    c = file.read()
                
                original = c
                for k, v in replacements.items():
                    c = c.replace(k, v)
                
                if c != original:
                    with open(path, 'w', encoding='utf-8') as file:
                        file.write(c)
                    count += 1
                    
print(f"Replaced in {count} files.")
