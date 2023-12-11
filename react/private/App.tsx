// NPM Modules
import Box from '@mui/system/Box';
import Paper from '@mui/material/Paper';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import { Switch } from '@mui/material';
import * as React from 'react';

// Custom Modules
interface Plugin {
  Name: string;
  PluginURI: string;
  Version: string;
  Description: string;
  Author: string;
  AuthorURI: string;
  TextDomain: string;
  DomainPath: string;
  Network: boolean;
  RequiresWP: string;
  RequiresPHP: string;
  UpdateURI: string;
  Title: string;
  AuthorName: string;
  isActive: boolean;
  pluginUsageType: PluginUsageType;
}

interface PluginUsageType {
  hasAPIRequests: boolean; // API Requests: Monitor for any outgoing API requests to the plugin’s servers or services.
  hasCustomTemplates: boolean; // Custom Templates: Look for custom templates or template overrides provided by the plugin.
  hasDatabaseQueries: boolean; // Database Queries: Analyze the database for tables or records that are specific to the plugin.
  hasDOMElements: boolean; // DOM Elements: Check the HTML source for specific DOM elements that the plugin might create.
  hasEnqueuedAssets: boolean; // Enqueued Scripts and Styles: Check if the plugin enqueues specific JavaScript or CSS files on the front-end.
  hasFilterActionHooks: boolean; // Filter and Action Hooks: Determine if the plugin adds specific filters or actions that are being executed.
  hasHTTPRequestRepsonse: boolean; // HTTP Requests and Responses: Inspect HTTP requests and responses for signs of the plugin's activity.
  hasPluginPHPFunctions: boolean; // Plugin-Specific PHP Functions: Look for plugin-specific PHP functions in the theme files.
  hasURLParams: boolean; // URL Parameters: Some plugins might append specific parameters to URLs.
  hasWidgets: boolean; // Widgets: See if the plugin provides widgets that are used in sidebars or widget areas.
  // post content specific
  hasCustomFields: boolean; // Custom Fields: Check for custom fields or post meta that the plugin might add to posts.
  hasCustomPostTypes: boolean; // Custom Post Types: Check if the plugin creates custom post types and if these are being used.
  hasMetaBoxes: boolean; // Meta Boxes: Identify if the plugin adds meta boxes to posts or pages, and if these are being utilized.
  hasShortcodes: boolean; // Shortcodes: Look for shortcodes that are specific to the plugin in the post content.
}

interface PageProps {
  child?: any;
}

interface PageState {
  pluginList: Plugin[];
}

export default class App extends React.Component<PageProps, PageState> {
  constructor(props: PageProps) {
    super(props);

    this.state = {
      pluginList: [],
    };
  }

  componentDidMount() {
    this.getPlugins().then((pluginList) => {
      this.setState({ pluginList: pluginList });
    });
  }

  async getPlugins() {
    try {
      const response = await fetch('/wp-json/front-end-monitor/v1/get-plugins', {
        method: 'GET',
      });
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      const data = await response.json();
      console.log('getPlugins fetch data:\n', data);
      return Array.isArray(data) ? data : []; // Ensure the data is an array
    } catch (error) {
      console.error('Error Received\n', error);
      throw error; // Re-throw the error so it can be handled by the caller
    }
  }

  render() {
    return (
      <Box
        id='front_end_plugin_monitor_react'
        component='section'
        className='front-end-plugin-page'
      >
        <div>
          <h1>Front-End Plugin Monitor</h1>
          <p>
            The following needs to be added to your `wp-config.php` file in order to work properly.
            <br />
            <br />
            <code>define('SAVEQUERIES', true);</code>
            <br />
            <br />
          </p>
        </div>
        <div>
          <TableContainer component={Paper}>
            <Table
              sx={{ minWidth: 650 }}
              aria-label='simple table'
            >
              <TableHead>
                <TableRow>
                  <TableCell>Plugin Name</TableCell>
                  <TableCell align='right'>Status</TableCell>
                  <TableCell align='right'>Unknown</TableCell>
                  <TableCell align='right'>Unknown</TableCell>
                  <TableCell align='right'>Enabled</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {this.state.pluginList.map((plugin: Plugin, index) => {
                  return (
                    <TableRow
                      className={plugin.isActive ? 'active' : 'inactive'}
                      key={index}
                    >
                      <TableCell>{plugin.Name}</TableCell>
                      <TableCell align='right'>{plugin.isActive ? 'Active' : 'Inactive'}</TableCell>
                      <TableCell align='right'>TBD</TableCell>
                      <TableCell align='right'>TBD</TableCell>
                      <TableCell align='right'>
                        <Switch
                          aria-label='Enabled'
                          color='warning'
                          defaultChecked={plugin.isActive}
                        />
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </TableContainer>
        </div>
      </Box>
    );
  }
}
