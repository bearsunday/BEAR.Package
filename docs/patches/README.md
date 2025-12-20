# Psalm Taint Annotation Patches

These patches add Psalm taint annotations to BEAR.Sunday ecosystem packages.

## Applying Patches

```bash
# BEAR.Resource
git clone https://github.com/bearsunday/BEAR.Resource.git
cd BEAR.Resource
git checkout -b add-psalm-taint-annotations
git am < bear-resource-taint.patch
git push -u origin add-psalm-taint-annotations

# Ray.AuraSqlModule
git clone https://github.com/ray-di/Ray.AuraSqlModule.git
cd Ray.AuraSqlModule
git checkout -b add-psalm-taint-annotations
git am < ray-aura-sql-module-taint.patch
git push -u origin add-psalm-taint-annotations

# Madapaja.TwigModule
git clone https://github.com/madapaja/Madapaja.TwigModule.git
cd Madapaja.TwigModule
git checkout -b add-psalm-taint-annotations
git am < madapaja-twig-module-taint.patch
git push -u origin add-psalm-taint-annotations

# Qiq
git clone https://github.com/qiqphp/qiq.git
cd qiq
git checkout -b add-psalm-taint-annotations
git am < qiq-taint.patch
git push -u origin add-psalm-taint-annotations
```

## Patch Contents

| Package | Annotations |
|---------|-------------|
| bear/resource | `@psalm-taint-source input` on params, `@psalm-taint-sink ssrf`, `@psalm-taint-escape html` |
| ray/aura-sql-module | `@psalm-taint-sink sql`, `@psalm-taint-escape sql` |
| madapaja/twig-module | `@psalm-taint-escape html` |
| qiq/qiq | `@psalm-taint-escape html`, `@psalm-taint-escape css` |
