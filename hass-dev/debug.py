from homeassistant import __main__ as hass_entry
import sys

def main():
    sys.exit(hass_entry.main())
if __name__ == "__main__":
    main()