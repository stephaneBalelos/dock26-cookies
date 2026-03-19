declare module "@orestbida/iframemanager" {
  export interface IframeManagerRunOptions<
    ServiceNames extends string = string,
  > {
    currLang: string;
    autoLang?: boolean;
    onChange?: (details: {
      changedServices: string[];
      eventSource: {
        type: "api" | "click";
        service: string;
        action: "accept" | "reject";
      };
    }) => void;

    services: Record<
      ServiceNames,
      {
        embedUrl?: string;
        thumbnailUrl?:
          | string
          | ((
              dataId: string,
              setThumbnail: (url: string) => void,
            ) => void | Promise<void>);

        iframe?: {
          allow?: string;
          params?: string;
          onload?: (
            dataId: string,
            setThumbnail: (url: string) => void,
          ) => void;
          [attr: string]: any;
        };

        cookie?: {
          name: string;
          path?: string;
          samesite?: string;
          domain?: string;
        };

        languages: Record<
          string,
          {
            notice: string;
            loadBtn: string;
            loadAllBtn: string;
          }
        >;
      }
    >;
  }

  export interface IframeManagerState {
    services: Map<string, boolean>;
    acceptedServices: string[];
  }

  export interface IframeManagerInstance {
    run: (options: IframeManagerRunOptions) => void;
    acceptService: (serviceName: string) => void;
    rejectService: (serviceName: string) => void;
    getState: () => IframeManagerState;
    getConfig: () => IframeManagerRunOptions<any>;
    reset: (hard?: boolean) => void;
  }

  function iframemanager(): IframeManagerInstance;
  export default iframemanager;
}
