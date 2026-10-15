import sys

with open('app/Filament/Admin/Resources/Solicituds/SolicitudResource.php', 'r') as f:
    content = f.read()

parts = content.split('<<<<<<< HEAD\n')

for i, part in enumerate(parts[1:]):
    print(f"Conflict {i+1}:")
    subparts = part.split('=======\n')
    if len(subparts) == 2:
        print("  Has '======='")
        # take the first few characters of ours and theirs
        ours = subparts[0][:100].replace('\n', ' ')
        theirs = subparts[1].split('>>>>>>> origin/desarrollo-vistas\n')[0][:100].replace('\n', ' ')
        print(f"  OURS: {ours}")
        print(f"  THEIRS: {theirs}")
    else:
        print("  MISSING '======='")
