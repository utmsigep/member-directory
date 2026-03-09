{
  description = "member-directory custom flox packages";

  inputs.nixpkgs.url = "github:flox/nixpkgs/stable";

  outputs = { self, nixpkgs }:
    let
      systems = [
        "aarch64-darwin"
        "x86_64-darwin"
        "aarch64-linux"
        "x86_64-linux"
      ];
      forAllSystems = f: nixpkgs.lib.genAttrs systems (system: f system);
    in {
      packages = forAllSystems (system:
        let
          pkgs = import nixpkgs { inherit system; };
          phpCustom = import ./.flox/pkgs/php.nix { pkgs = pkgs; };
          composerCustom = phpCustom.packages.composer;
        in {
          php-custom = phpCustom;
          composer-custom = composerCustom;
          default = phpCustom;
        }
      );
    };
}
