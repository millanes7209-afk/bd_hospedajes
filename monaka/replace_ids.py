import sys

files = [
    r"c:\xampp\htdocs\dulces\monaka\resources\views\menu\index.blade.php",
    r"c:\xampp\htdocs\dulces\monaka\resources\views\order.blade.php"
]

for p in files:
    with open(p, 'r', encoding='utf-8') as f:
        c = f.read()
    c = c.replace('productoID', 'producto_id').replace('varianteID', 'variante_id')
    with open(p, 'w', encoding='utf-8') as f:
        f.write(c)

print(f"Successfully processed {len(files)} files.")
